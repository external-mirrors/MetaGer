#!/usr/bin/env bash
#
# Trim the container registry to the images that are still worth keeping.
#
# Runs in every pipeline. Two sweeps, because images accumulate two different
# ways and only one of them is fixed by branches being deleted:
#
#   this ref     A long-lived branch — development, master, or a merge request
#                that goes through forty rounds of review — builds an fpm, an
#                nginx, a node and a composer image on every single push. Nine
#                deployed revisions are worth keeping so a rollback has
#                somewhere to go. The other several hundred are not, and nothing
#                will ever ask for them again.
#
#   dead refs    A merged merge request takes its branch with it, and with the
#                branch goes any chance of the per-ref sweep above ever running
#                for it again. Whatever that branch had built at the moment it
#                was merged stays in the registry for ever. That is how this
#                repository came to hold 198 tags for branches that no longer
#                existed, the oldest from 2022.
#
# ## What is protected, and why the protection has to be explicit
#
# The obvious rule — "delete every tag for this ref that no helm revision
# names" — is the rule this script used to implement, and it is wrong in one
# specific case that costs a deployment. Two pipelines running on the same ref:
# the older one reaches this job while the newer one has just pushed its images
# and has not deployed them yet. Those images are named by no revision, so the
# older pipeline deletes them, and the newer pipeline's deploy fails with an
# ImagePullBackOff and a tag that "was not found" minutes after it was pushed.
# That is not hypothetical; it cost three pipeline round trips to diagnose.
#
# So a tag is kept if any of these holds:
#
#   * one of the last $KEEP_N helm revisions of this release deploys it
#   * this pipeline built it
#   * it belongs to the commit the ref currently points at
#
# and a pipeline that is not running on the ref's current HEAD does not sweep
# that ref at all — it has been superseded, and the pipeline that superseded it
# will do the sweep with better information a few minutes later.
#
# Together those two rules make the race unreachable from either side. The older
# pipeline will not delete anything, and if it somehow does run, the newer
# pipeline's images are the HEAD ones and are protected by name.
#
# Both facts come from the branch list, which is the one endpoint that matters
# here: it is on GitLab's allowlist for CI_JOB_TOKEN, so this needs no extra
# credential. The pipeline list would have been the more direct signal and is
# *not* on that list — asking for it gets a 401 and would have left this sweep
# quietly disabled. If the branch list ever becomes unreadable too, both sweeps
# are skipped rather than run blind: leaking images costs storage, deleting a
# live one costs an outage.
set -uo pipefail

source "$(dirname "${BASH_SOURCE[0]}")/registry_api.sh"

HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:53}
HELM_RELEASE_NAME=$(echo "$HELM_RELEASE_NAME" | sed 's/-$//')

KEEP_N="${KEEP_N:-9}"

REPOSITORIES=("$FPM_REPOSITORY_ID" "$NGINX_REPOSITORY_ID" "$NODE_REPOSITORY_ID")

# Keyed "<repository id> <tag>", not by tag alone. The fpm, nginx and node
# images of one commit all carry the same tag string, so a bare-tag set would
# let a deployed fpm image protect the node image that happens to share its
# name — and node images are build-time only, never deployed, and are precisely
# what fills the registry up. 163 of the 186 node tags here were dead.
declare -A protected=()

protect() { protected["$1 $2"]=1; }

# ---------------------------------------------------------------------------
# Protected: the last $KEEP_N revisions of this release
# ---------------------------------------------------------------------------
#
# helm history returns oldest first, so the tail is the recent end. A release
# that does not exist yet is the normal state of a branch's first pipeline and
# not an error — it simply protects nothing.
protect_deployed_revisions() {
    local history revisions revision values

    if ! history="$(helm -n "$KUBE_NAMESPACE" history "$HELM_RELEASE_NAME" -o json 2>/dev/null)"; then
        echo "Release $HELM_RELEASE_NAME does not exist yet — no deployed revisions to protect."
        return
    fi

    revisions="$(printf '%s' "$history" | jq -r ".[-${KEEP_N}:][].revision")"
    for revision in $revisions; do
        values="$(helm -n "$KUBE_NAMESPACE" get values "$HELM_RELEASE_NAME" --revision="$revision" -o json)"
        protect "$FPM_REPOSITORY_ID"   "$(printf '%s' "$values" | jq -r '.image.fpm.tag')"
        protect "$NGINX_REPOSITORY_ID" "$(printf '%s' "$values" | jq -r '.image.nginx.tag')"
    done

    echo "Protecting ${#protected[@]} tag(s) from the last $KEEP_N revision(s) of $HELM_RELEASE_NAME."
}

# ---------------------------------------------------------------------------
# The branch list: which refs are still alive, and where each one points
# ---------------------------------------------------------------------------
#
# One call answers both questions this script has to ask. Returns non-zero when
# it could not be made, which is the signal to sweep nothing at all.
read_branches() {
    local branches releases branch slug head release

    if ! branches="$(api_get_paged "$API/repository/branches?per_page=100")"; then
        return 1
    fi

    declare -gA live_slug=()
    declare -gA head_sha=()
    while read -r branch head; do
        [[ -n "$branch" ]] || continue
        slug="$(ref_slug "$branch")"
        live_slug["$slug"]=1
        head_sha["$slug"]="$head"
    done < <(printf '%s' "$branches" | jq -r '.[] | "\(.name) \(.commit.id)"')

    # -a, so a release stuck in failed or pending-upgrade still counts as
    # deployed. Its pods are running and will restart; `helm list` hides those
    # states by default, which would make the release invisible here and its
    # images fair game the moment the branch went away.
    releases="$(helm -n "$KUBE_NAMESPACE" list -a -o json 2>/dev/null | jq -r '.[].name' || true)"
    declare -gA deployed_release=()
    for release in $releases; do
        deployed_release["$release"]=1
    done

    echo "Found ${#live_slug[@]} branch(es) and ${#deployed_release[@]} helm release(s)."
}

# ---------------------------------------------------------------------------
# Protected: this pipeline's images, and the ref's current HEAD
# ---------------------------------------------------------------------------
protect_this_pipeline_and_head() {
    protect "$FPM_REPOSITORY_ID"   "$DOCKER_FPM_IMAGE_TAG"
    protect "$FPM_REPOSITORY_ID"   "$DOCKER_COMPOSER_IMAGE_TAG"
    protect "$NGINX_REPOSITORY_ID" "$DOCKER_NGINX_IMAGE_TAG"
    protect "$NODE_REPOSITORY_ID"  "$DOCKER_NODE_IMAGE_TAG"

    local head="${head_sha[$DOCKER_IMAGE_TAG_PREFIX]:-}"
    [[ -n "$head" ]] || return 0

    protect "$FPM_REPOSITORY_ID"   "${DOCKER_IMAGE_TAG_PREFIX}-${head}"
    protect "$FPM_REPOSITORY_ID"   "${DOCKER_IMAGE_TAG_PREFIX}-composer-${head}"
    protect "$NGINX_REPOSITORY_ID" "${DOCKER_IMAGE_TAG_PREFIX}-${head}"
    protect "$NODE_REPOSITORY_ID"  "${DOCKER_IMAGE_TAG_PREFIX}-${head}"
}

# Whether this pipeline is the one that should sweep its own ref.
#
# A merge request pipeline runs on the source branch, so its own commit is that
# branch's HEAD — unless something was pushed after it started, which is exactly
# the case this is here to catch.
is_current_for_this_ref() {
    local head="${head_sha[$DOCKER_IMAGE_TAG_PREFIX]:-}"
    local mine="${CI_MERGE_REQUEST_SOURCE_BRANCH_SHA:-$CI_COMMIT_SHA}"

    [[ -n "$head" && "$head" == "$mine" ]]
}

slug_is_dead() {
    local slug="$1" release

    [[ -v live_slug["$slug"] ]] && return 1

    release="review-$slug"
    release="${release:0:53}"
    release="${release%-}"
    [[ -v deployed_release["$release"] ]] && return 1
    [[ -v deployed_release["$slug"] ]] && return 1

    return 0
}

# ---------------------------------------------------------------------------

sweep_this_ref=1
sweep_dead_refs=1

protect_deployed_revisions

if read_branches; then
    protect_this_pipeline_and_head

    if ! is_current_for_this_ref; then
        echo "Something has been pushed to $DOCKER_IMAGE_TAG_PREFIX since this pipeline started."
        echo "Leaving its images alone — the pipeline for the newer commit will sweep them."
        sweep_this_ref=0
    fi
else
    echo "WARNING: could not read the branch list. Without it there is no way to tell" >&2
    echo "         a superseded image from one another pipeline is about to deploy, nor" >&2
    echo "         which branches still exist. Sweeping nothing. Set CLEANUP_API_TOKEN" >&2
    echo "         to a project access token with read_api if CI_JOB_TOKEN has lost" >&2
    echo "         access to GET /projects/:id/repository/branches." >&2
    sweep_this_ref=0
    sweep_dead_refs=0
fi

for repository in "${REPOSITORIES[@]}"; do
    echo
    echo "Sweeping repository $repository..."

    while read -r tag; do
        [[ -n "$tag" ]] || continue
        [[ -v protected["$repository $tag"] ]] && continue

        slug="$(tag_ref_slug "$tag")"

        if [[ "$slug" == "$DOCKER_IMAGE_TAG_PREFIX" ]]; then
            [[ $sweep_this_ref -eq 1 ]] || continue
        else
            [[ $sweep_dead_refs -eq 1 ]] || continue
            slug_is_dead "$slug" || continue
        fi

        registry_delete_tag "$repository" "$tag"
    done < <(registry_tags "$repository")
done
