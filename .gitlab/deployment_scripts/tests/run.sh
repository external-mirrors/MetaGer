#!/usr/bin/env bash
#
# Tests for the registry cleanup scripts.
#
#   ./tests/run.sh
#
# These scripts decide which container images get deleted, and they run with a
# token that can delete them for real. That is not something to leave uncovered:
# deleting one tag too many takes a running deployment down with an
# ImagePullBackOff, and deleting one too few is invisible until the registry has
# four years of images in it — which is exactly where this repository ended up
# (198 tags for branches that no longer existed).
#
# curl and helm are stubbed (see stubs/), so nothing here reaches the network or
# a cluster. The assertions are all of the form "these tags were deleted and no
# others", because that is the only observable behaviour that matters.
set -uo pipefail

cd "$(dirname "$0")"

SCRIPTS="$(cd .. && pwd)"
STUBS="$PWD/stubs"

failures=0
tests=0

pass() { printf '  ok   %s\n' "$1"; }
fail() {
    printf '  FAIL %s\n' "$1" >&2
    shift
    for line in "$@"; do printf '       %s\n' "$line" >&2; done
    failures=$((failures + 1))
}

# Forty hex characters, because the cleanup has to be able to tell a commit sha
# at the end of a tag from the branch name in front of it — and branch names in
# this repository contain hyphens and digits, so "the last segment" is not a
# workable rule.
sha() { printf '%s' "$1$1$1$1$1$1$1$1" | cut -c1-40; }

SHA_CURRENT=$(sha 11111)
SHA_DEPLOYED_1=$(sha aaaaa)
SHA_DEPLOYED_2=$(sha bbbbb)
SHA_EXPIRED=$(sha ccccc)
SHA_NEWER=$(sha 22222)
SHA_NEVER_DEPLOYED=$(sha 33333)
SHA_ORPHAN=$(sha 44444)
SHA_MASTER=$(sha 55555)

PREFIX="1366-dependency-update"
ORPHAN_PREFIX="1216-improve-localization"

# A registry and a helm history that between them contain every case these
# scripts have to tell apart.
scenario() {
    WORK="$(mktemp -d)"
    FIXTURE_DIR="$WORK/fixtures"
    RECORD_FILE="$WORK/deleted.txt"
    mkdir -p "$FIXTURE_DIR"
    : >"$RECORD_FILE"

    cat >"$FIXTURE_DIR/tags-418.txt" <<EOF
$PREFIX-$SHA_CURRENT
$PREFIX-composer-$SHA_CURRENT
$PREFIX-$SHA_DEPLOYED_1
$PREFIX-$SHA_DEPLOYED_2
$PREFIX-$SHA_EXPIRED
$PREFIX-$SHA_NEWER
$PREFIX-composer-$SHA_NEWER
$PREFIX-$SHA_NEVER_DEPLOYED
$ORPHAN_PREFIX-$SHA_ORPHAN
$ORPHAN_PREFIX-composer-$SHA_ORPHAN
master-$SHA_MASTER
EOF

    cat >"$FIXTURE_DIR/tags-416.txt" <<EOF
$PREFIX-$SHA_CURRENT
$PREFIX-$SHA_DEPLOYED_1
$PREFIX-$SHA_DEPLOYED_2
$PREFIX-$SHA_EXPIRED
$PREFIX-$SHA_NEWER
$ORPHAN_PREFIX-$SHA_ORPHAN
master-$SHA_MASTER
EOF

    cat >"$FIXTURE_DIR/tags-419.txt" <<EOF
$PREFIX-$SHA_CURRENT
$PREFIX-$SHA_DEPLOYED_1
$PREFIX-$SHA_NEWER
$ORPHAN_PREFIX-$SHA_ORPHAN
master-$SHA_MASTER
EOF

    # Three revisions, oldest first — the order `helm history` returns.
    cat >"$FIXTURE_DIR/helm-history.json" <<EOF
[{"revision": 1}, {"revision": 2}, {"revision": 3}]
EOF
    revision_values 1 "$SHA_EXPIRED"
    revision_values 2 "$SHA_DEPLOYED_1"
    revision_values 3 "$SHA_DEPLOYED_2"
}

revision_values() {
    cat >"$FIXTURE_DIR/helm-values-$1.json" <<EOF
{"image": {"fpm": {"tag": "$PREFIX-$2"}, "nginx": {"tag": "$PREFIX-$2"}}}
EOF
}

# Run one of the scripts against the staged scenario.
run() {
    local script="$1"; shift
    (
        cd "$WORK" || exit 1
        export PATH="$STUBS:$PATH"
        export FIXTURE_DIR RECORD_FILE
        export CI_API_V4_URL="https://gitlab.example/api/v4"
        export CI_PROJECT_ID=1
        export CI_JOB_TOKEN=token
        export CI_COMMIT_SHA="$SHA_CURRENT"
        export KUBE_NAMESPACE=metager
        export HELM_RELEASE_NAME="review-$PREFIX"
        export DOCKER_IMAGE_TAG_PREFIX="$PREFIX"
        export DOCKER_FPM_IMAGE_TAG="$PREFIX-$SHA_CURRENT"
        export DOCKER_NGINX_IMAGE_TAG="$PREFIX-$SHA_CURRENT"
        export DOCKER_NODE_IMAGE_TAG="$PREFIX-$SHA_CURRENT"
        export DOCKER_COMPOSER_IMAGE_TAG="$PREFIX-composer-$SHA_CURRENT"
        export FPM_REPOSITORY_ID=418
        export NGINX_REPOSITORY_ID=416
        export NODE_REPOSITORY_ID=419
        for assignment in "$@"; do export "${assignment?}"; done
        "$SCRIPTS/$script" >"$WORK/output.txt" 2>&1
        echo $? >"$WORK/status.txt"
    )
}

deleted() { sort "$RECORD_FILE"; }

assert_deleted() {
    local name="$1"; shift
    local expected actual
    expected="$(printf '%s\n' "$@" | sort)"
    actual="$(deleted)"
    tests=$((tests + 1))
    if [[ "$expected" == "$actual" ]]; then
        pass "$name"
    else
        fail "$name" "expected these deletions:" "${expected:-  (none)}" "but got:" "${actual:-  (none)}"
    fi
}

# VERBOSE=1 prints what the script under test actually said. Worth having: every
# failure mode of these scripts is "it skipped a sweep and told you why", and the
# assertion alone only shows that nothing was deleted.
cleanup() {
    [[ -n "${VERBOSE:-}" ]] && { echo "--- script output:"; sed 's/^/       /' "$WORK/output.txt"; }
    rm -rf "$WORK"
}

# The registry state the fixtures describe, restated as the facts the assertions
# below turn on:
#
#   deployed, kept      SHA_DEPLOYED_1 / SHA_DEPLOYED_2  (revisions 2 and 3)
#   deployed, expired   SHA_EXPIRED                      (revision 1, past KEEP_N=2)
#   this pipeline       SHA_CURRENT
#   pushed, undeployed  SHA_NEWER           — whichever commit the scenario makes
#                                             the branch HEAD: newer than this
#                                             pipeline under
#                                             superseded_by_a_newer_push, an
#                                             ordinary superseded build under
#                                             live_branches
#   pushed, undeployed  SHA_NEVER_DEPLOYED  — a pipeline that finished and failed
#   another live branch master-SHA_MASTER
#   a deleted branch    $ORPHAN_PREFIX-SHA_ORPHAN

# This pipeline is the newest thing on its branch — the ordinary case.
live_branches() {
    branches "$SHA_CURRENT"
}

# Something was pushed after this pipeline started, so its commit is no longer
# the branch HEAD. The case that cost three pipeline round trips to diagnose.
superseded_by_a_newer_push() {
    branches "$SHA_NEWER"
}

branches() {
    cat >"$FIXTURE_DIR/branches.json" <<EOF
[{"name": "master",       "commit": {"id": "$SHA_MASTER"}},
 {"name": "development",  "commit": {"id": "$(sha 99999)"}},
 {"name": "$PREFIX", "commit": {"id": "$1"}}]
EOF
}

no_review_releases() { echo '[]' >"$FIXTURE_DIR/helm-list.json"; }

# ---------------------------------------------------------------------------

test_keeps_the_last_n_deployed_revisions() {
    scenario; live_branches; no_review_releases
    run cleanup_tags.sh KEEP_N=2

    # SHA_EXPIRED falls off the end of the kept window, so its fpm and nginx
    # images go. SHA_DEPLOYED_1 and _2 are the window and stay.
    assert_deleted "keeps the last KEEP_N deployed revisions and expires the rest" \
        "418 $PREFIX-$SHA_EXPIRED" \
        "416 $PREFIX-$SHA_EXPIRED" \
        "418 $PREFIX-$SHA_NEVER_DEPLOYED" \
        "418 $PREFIX-$SHA_NEWER" \
        "418 $PREFIX-composer-$SHA_NEWER" \
        "416 $PREFIX-$SHA_NEWER" \
        "419 $PREFIX-$SHA_NEWER" \
        "418 $ORPHAN_PREFIX-$SHA_ORPHAN" \
        "418 $ORPHAN_PREFIX-composer-$SHA_ORPHAN" \
        "416 $ORPHAN_PREFIX-$SHA_ORPHAN" \
        "419 $ORPHAN_PREFIX-$SHA_ORPHAN" \
        "419 $PREFIX-$SHA_DEPLOYED_1"
    cleanup
}

test_never_deletes_an_image_a_newer_pipeline_just_pushed() {
    scenario; superseded_by_a_newer_push; no_review_releases
    run cleanup_tags.sh KEEP_N=2

    # Two commits a minute apart. This pipeline is the older one; the newer one
    # has pushed its images and has not deployed them, so no helm revision names
    # them. Deleting them fails the newer deploy with an ImagePullBackOff on a
    # tag that was pushed minutes earlier — and being a deploy failure rather
    # than a cleanup failure, it gets diagnosed nowhere near here.
    tests=$((tests + 1))
    if grep -q "$SHA_NEWER" "$RECORD_FILE"; then
        fail "never deletes an image a newer pipeline just pushed" \
            "deleted:" "$(grep "$SHA_NEWER" "$RECORD_FILE")"
    else
        pass "never deletes an image a newer pipeline just pushed"
    fi
    cleanup
}

test_a_superseded_pipeline_sweeps_nothing_on_its_own_ref() {
    scenario; superseded_by_a_newer_push; no_review_releases
    run cleanup_tags.sh KEEP_N=2

    # Not merely "spares the newer images" — it leaves the whole ref alone and
    # lets the newer pipeline do the sweep, because its own view of what is
    # deployed and what is superseded is a few minutes out of date.
    assert_deleted "a superseded pipeline leaves its own ref entirely alone" \
        "418 $ORPHAN_PREFIX-$SHA_ORPHAN" \
        "418 $ORPHAN_PREFIX-composer-$SHA_ORPHAN" \
        "416 $ORPHAN_PREFIX-$SHA_ORPHAN" \
        "419 $ORPHAN_PREFIX-$SHA_ORPHAN"
    cleanup
}

test_deletes_images_of_a_pipeline_that_never_deployed() {
    scenario; live_branches; no_review_releases
    run cleanup_tags.sh KEEP_N=2

    # The counterpart to the test above, and the reason the guard has to be
    # "unfinished pipeline" and not "any pipeline": a long-lived branch whose
    # test job fails forty times must not leave forty images behind.
    tests=$((tests + 1))
    if grep -q "418 $PREFIX-$SHA_NEVER_DEPLOYED" "$RECORD_FILE"; then
        pass "deletes images from a finished pipeline that never deployed"
    else
        fail "deletes images from a finished pipeline that never deployed" \
            "$PREFIX-$SHA_NEVER_DEPLOYED survived; on a branch under active review" \
            "these are most of what the registry fills up with."
    fi
    cleanup
}

test_deletes_everything_for_a_branch_that_is_gone() {
    scenario; live_branches; no_review_releases
    run cleanup_tags.sh KEEP_N=2

    tests=$((tests + 1))
    local missing=()
    for expected in "418 $ORPHAN_PREFIX-$SHA_ORPHAN" \
                    "418 $ORPHAN_PREFIX-composer-$SHA_ORPHAN" \
                    "416 $ORPHAN_PREFIX-$SHA_ORPHAN" \
                    "419 $ORPHAN_PREFIX-$SHA_ORPHAN"; do
        grep -qxF "$expected" "$RECORD_FILE" || missing+=("$expected")
    done
    if [[ ${#missing[@]} -eq 0 ]]; then
        pass "deletes every image of a branch that no longer exists"
    else
        fail "deletes every image of a branch that no longer exists" "${missing[@]}"
    fi
    cleanup
}

test_leaves_other_live_branches_alone() {
    scenario; live_branches; no_review_releases
    run cleanup_tags.sh KEEP_N=2

    tests=$((tests + 1))
    if grep -q "master-$SHA_MASTER" "$RECORD_FILE"; then
        fail "leaves other live branches alone" \
            "master's image was deleted by a merge request's pipeline."
    else
        pass "leaves other live branches alone"
    fi
    cleanup
}

test_spares_a_dead_branch_whose_review_app_is_still_up() {
    scenario; live_branches
    # The branch is gone but its release has not been torn down yet — the stop
    # job races the push that deletes the branch.
    printf '[{"name": "review-%s"}]\n' "$ORPHAN_PREFIX" >"$FIXTURE_DIR/helm-list.json"
    run cleanup_tags.sh KEEP_N=2

    tests=$((tests + 1))
    if grep -q "$ORPHAN_PREFIX" "$RECORD_FILE"; then
        fail "spares a dead branch whose review app is still deployed" \
            "Its pods would fail to pull on the next restart."
    else
        pass "spares a dead branch whose review app is still deployed"
    fi
    cleanup
}

test_deletes_nothing_when_the_branch_list_is_unreadable() {
    scenario; live_branches; no_review_releases
    touch "$FIXTURE_DIR/branches-forbidden"
    run cleanup_tags.sh KEEP_N=2

    # The branch list is the only thing standing between this script and
    # deleting an image another pipeline is about to deploy. Without it, it does
    # nothing at all — the registry filling up is recoverable, a deploy failing
    # on a missing image is not, and the two are not close in cost.
    assert_deleted "deletes nothing at all when the branch list cannot be read"
    cleanup
}

test_survives_a_branchs_first_pipeline() {
    scenario; live_branches; no_review_releases
    rm "$FIXTURE_DIR/helm-history.json"
    run cleanup_tags.sh KEEP_N=2

    tests=$((tests + 1))
    if [[ "$(cat "$WORK/status.txt")" == "0" ]]; then
        pass "survives the first pipeline of a branch, when no release exists"
    else
        fail "survives the first pipeline of a branch, when no release exists" \
            "exited $(cat "$WORK/status.txt")" "$(cat "$WORK/output.txt")"
    fi
    cleanup
}

test_purge_removes_node_and_composer_images_too() {
    scenario; live_branches; no_review_releases
    run purge_tags.sh

    # Teardown reads no helm history, so the build-only images are visible to it.
    # The old teardown path could not see them at all, which is where 163 of the
    # 186 node tags in this registry came from.
    assert_deleted "purge removes every image of the ref, node and composer included" \
        "418 $PREFIX-$SHA_CURRENT" \
        "418 $PREFIX-composer-$SHA_CURRENT" \
        "418 $PREFIX-$SHA_DEPLOYED_1" \
        "418 $PREFIX-$SHA_DEPLOYED_2" \
        "418 $PREFIX-$SHA_EXPIRED" \
        "418 $PREFIX-$SHA_NEWER" \
        "418 $PREFIX-composer-$SHA_NEWER" \
        "418 $PREFIX-$SHA_NEVER_DEPLOYED" \
        "416 $PREFIX-$SHA_CURRENT" \
        "416 $PREFIX-$SHA_DEPLOYED_1" \
        "416 $PREFIX-$SHA_DEPLOYED_2" \
        "416 $PREFIX-$SHA_EXPIRED" \
        "416 $PREFIX-$SHA_NEWER" \
        "419 $PREFIX-$SHA_CURRENT" \
        "419 $PREFIX-$SHA_DEPLOYED_1" \
        "419 $PREFIX-$SHA_NEWER"
    cleanup
}

test_purge_does_not_match_a_longer_branch_name() {
    scenario; live_branches; no_review_releases
    # A real pair from this repository's history: 1207-add-infotiger-to-listing-
    # of-used-se and …-se-2 both existed. A glob on the tag would purge both.
    echo "$PREFIX-2-$SHA_MASTER" >>"$FIXTURE_DIR/tags-418.txt"
    run purge_tags.sh

    tests=$((tests + 1))
    if grep -q "$PREFIX-2-$SHA_MASTER" "$RECORD_FILE"; then
        fail "purge does not reach a branch whose name merely starts the same" \
            "Purging $PREFIX also purged $PREFIX-2."
    else
        pass "purge does not reach a branch whose name merely starts the same"
    fi
    cleanup
}

test_ref_slug_matches_gitlabs() {
    scenario
    tests=$((tests + 1))
    local result
    result="$(
        export PATH="$STUBS:$PATH" CI_API_V4_URL=x CI_PROJECT_ID=1
        source "$SCRIPTS/registry_api.sh"
        ref_slug "1366-Dependency_Update"
        echo -n " "
        tag_ref_slug "1366-dependency-update-composer-$SHA_CURRENT"
        echo -n " "
        tag_ref_slug "1366-dependency-update-$SHA_CURRENT"
    )"
    if [[ "$result" == "1366-dependency-update 1366-dependency-update 1366-dependency-update" ]]; then
        pass "a branch name and both of its tag shapes reduce to the same ref slug"
    else
        fail "a branch name and both of its tag shapes reduce to the same ref slug" "got: $result"
    fi
    cleanup
}

for test in $(declare -F | awk '{print $3}' | grep '^test_'); do
    "$test"
done

echo
if [[ $failures -eq 0 ]]; then
    echo "$tests assertions, all passing."
else
    echo "$tests assertions, $failures failing." >&2
    exit 1
fi
