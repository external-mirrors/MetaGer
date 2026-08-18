#!/usr/bin/env bash
#
# Shared helpers for the registry cleanup scripts. Sourced, not executed.
#
# ## Authentication
#
# Everything here goes through api_get/api_get_paged/registry_delete_tag, which
# send CI_JOB_TOKEN unless CLEANUP_API_TOKEN is set.
#
# GitLab restricts the job token to a fixed allowlist of endpoints, and the two
# this needs are on it: the registry tag endpoints (list and delete) and
# `GET /projects/:id/repository/branches`. Worth stating because the neighbouring
# endpoint is not — `GET /projects/:id/pipelines` is job-token-forbidden, and a
# cleanup written against it would answer 401 and disable itself in a way that
# looks exactly like "there was nothing to clean up".
#
# api_get reports failure rather than aborting so the caller can decide, and the
# decision is always to skip the sweep that needed the answer: a cleanup that
# guesses deletes an image out from under a running deployment. If that ever
# starts happening, a project access token with `read_api` in CLEANUP_API_TOKEN
# restores it without any other change.

API="$CI_API_V4_URL/projects/$CI_PROJECT_ID"

if [[ -n "${CLEANUP_API_TOKEN:-}" ]]; then
    AUTH_HEADER="PRIVATE-TOKEN: $CLEANUP_API_TOKEN"
else
    AUTH_HEADER="JOB-TOKEN: ${CI_JOB_TOKEN:-}"
fi

# GET one page. Prints the body; returns non-zero on any HTTP error, leaving the
# caller to decide whether that is fatal.
api_get() {
    curl --fail --silent -H "$AUTH_HEADER" "$1"
}

# GET every page of a paginated collection, printing the concatenated bodies.
#
# Paging is driven by the x-next-page response header rather than by counting,
# because that is what GitLab documents and because a collection that changes
# size mid-walk — which a registry being written to by another pipeline does —
# makes any offset arithmetic wrong. The 50-page ceiling is a runaway guard: at
# 100 per page that is 5000 tags, far more than this project will ever hold.
api_get_paged() {
    local url="$1"
    local headers page=1 counter=1 separator body

    headers="$(mktemp)"
    trap 'rm -f "$headers"' RETURN

    while [[ -n "$page" && $counter -le 50 ]]; do
        separator="?"
        [[ "$url" == *"?"* ]] && separator="&"

        if ! body="$(curl --fail --silent -D "$headers" -H "$AUTH_HEADER" "${url}${separator}page=${page}")"; then
            return 1
        fi
        printf '%s\n' "$body"

        # grep/cut rather than awk: IGNORECASE is a gawk extension and the deploy
        # image is Alpine, where awk is busybox and would silently match nothing.
        page="$(tr -d '\r' <"$headers" | grep -i '^x-next-page:' | cut -d: -f2 | tr -d '[:space:]')"
        counter=$((counter + 1))
        [[ -n "$page" ]] && sleep 1
    done

    # Explicit, because the loop's status is that of its last command — the
    # `sleep` guard, which is false on the final page. Without this the function
    # reports failure exactly when it succeeded in full, and every caller that
    # branches on its status takes the "API not permitted" path forever.
    return 0
}

# Every tag name in one registry repository.
registry_tags() {
    api_get_paged "$API/registry/repositories/$1/tags?per_page=100" | jq -r '.[].name'
}

registry_delete_tag() {
    local repository="$1" tag="$2"
    # --fail so a tag that has already gone is reported rather than counted as a
    # success; the callers tolerate it, but silently is not the same as knowingly.
    if curl --fail --silent -X DELETE -H "$AUTH_HEADER" "$API/registry/repositories/$repository/tags/$tag"; then
        printf '  deleted %s\n' "$tag"
    else
        printf '  could not delete %s (already gone?)\n' "$tag" >&2
    fi
}

# The ref slug a tag belongs to.
#
# Tags are "<ref slug>-<sha>" and, for the composer stage, "<ref slug>-composer-
# <sha>". Ref slugs in this repository contain both hyphens and digits
# ("1366-dependency-update"), so the branch name cannot be recovered by cutting
# at a separator — the only fixed part is the forty hex characters at the end.
tag_ref_slug() {
    local tag="$1"
    tag="$(printf '%s' "$tag" | sed -E 's/-[0-9a-f]{40}$//')"
    printf '%s' "${tag%-composer}"
}

# GitLab's own ref slug transform, so branch names can be compared against tag
# prefixes: lowercased, everything outside [0-9a-z] collapsed to a hyphen,
# trimmed, and cut to 63 characters with any trailing hyphen removed.
ref_slug() {
    local slug
    slug="$(printf '%s' "$1" |
        tr '[:upper:]' '[:lower:]' |
        sed -E 's/[^0-9a-z]+/-/g; s/^-+//; s/-+$//' |
        cut -c1-63 |
        sed -E 's/-+$//')"
    printf '%s' "$slug"
}
