#!/usr/bin/env bash
#
# Delete every image built for one ref. Run when its environment is stopped.
#
# The sweep in cleanup_tags.sh keeps the last few deployed revisions of a ref so
# a rollback has somewhere to go. Once the environment is being torn down there
# is nothing to roll back to, so the whole ref goes — including the two kinds of
# tag the old cleanup never touched at teardown:
#
#   * the node and composer images, which are build-time only and so appear in
#     no helm revision. The teardown path read tags out of the helm history, so
#     it could not see them at all: 163 of the 186 node tags in this registry
#     belonged to branches that had been deleted, some of them in 2022.
#   * fpm and nginx images from pipelines that built but never deployed — a
#     failed test job, a merge request closed before review.
#
# Deliberately prefix-driven rather than history-driven, so it does not matter
# whether the release still exists or in what order the teardown steps ran.
set -uo pipefail

source "$(dirname "${BASH_SOURCE[0]}")/registry_api.sh"

: "${DOCKER_IMAGE_TAG_PREFIX:?refusing to purge without a ref prefix}"

echo "Purging every image tag for $DOCKER_IMAGE_TAG_PREFIX..."

for repository in "$FPM_REPOSITORY_ID" "$NGINX_REPOSITORY_ID" "$NODE_REPOSITORY_ID"; do
    echo
    echo "Repository $repository:"

    while read -r tag; do
        [[ -n "$tag" ]] || continue
        # Exact-prefix match on the ref slug, not a glob on the tag. A glob would
        # make "1216-improve-localization" also match a branch called
        # "1216-improve-localization-2", and purge a live branch's images along
        # with the dead one's.
        [[ "$(tag_ref_slug "$tag")" == "$DOCKER_IMAGE_TAG_PREFIX" ]] || continue
        registry_delete_tag "$repository" "$tag"
    done < <(registry_tags "$repository")
done
