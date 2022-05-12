#!/bin/bash

# Call script with KEEP_N variable set to specify the amount of releases to keep

FPM_REPOSITORY_ID=418
NGINX_REPOSITORY_ID=416

helm -n $KUBE_NAMESPACE history ${CI_COMMIT_REF_SLUG} > /dev/null 2>&1
if [ $? -ne 0 ]
then
  echo "Release does not exist yet. Nothing to cleanup!"
  exit 0
fi

set -e

revision_count=$(helm -n $KUBE_NAMESPACE history $CI_COMMIT_REF_SLUG -o json | jq -r '. | length')

# Get the latest used image tags to make sure they are not deleted
latest_revision_values=$(helm -n $KUBE_NAMESPACE get values $CI_COMMIT_REF_SLUG -o json)
latest_fpm_tag=$(echo $latest_revision_values | jq -r '.image.fpm.tag')
latest_nginx_tag=$(echo $latest_revision_values | jq -r '.image.fpm.tag')

# Get List of revisions to expire (delete the image tags)
end_index=$(($KEEP_N > $revision_count ? 0 : $revision_count-$KEEP_N))
expired_revisions=$(helm -n $KUBE_NAMESPACE history $CI_COMMIT_REF_SLUG -o json | jq -r ".[0:$end_index][][\"revision\"]")

# Loop through those revisions
declare -A expired_fpm_tags
declare -A expired_nginx_tags
for revision in $expired_revisions
do
    # Get Values for this revision
    revision_values=$(helm -n $KUBE_NAMESPACE get values $CI_COMMIT_REF_SLUG --revision=$revision -ojson)
    # Get Image Tags for this revision
    revision_fpm_tag=$(echo $revision_values | jq -r '.image.fpm.tag')
    revision_nginx_tag=$(echo $revision_values | jq -r '.image.nginx.tag')

    # Add Tags to the arrays if they are not the latest
    if [ "$revision_fpm_tag" != "$latest_fpm_tag" ]
    then
        expired_fpm_tags[$revision_fpm_tag]=0
    fi

    if [ "$revision_nginx_tag" != "$latest_nginx_tag" ]
    then
        expired_nginx_tags[$revision_nginx_tag]=0
    fi
done

# Delete all gathered fpm tags
for fpm_tag in ${!expired_fpm_tags[@]}
do
    echo "Deleting fpm tag $fpm_tag"
    curl --fail -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$FPM_REPOSITORY_ID/tags/$fpm_tag"
    echo ""
done
# Delete all gathered nginx tags
for nginx_tag in ${!expired_nginx_tags[@]}
do
    echo "Deleting nginx tag $nginx_tag"
    curl --fail -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$FPM_REPOSITORY_ID/tags/$nginx_tag"
    echo ""
done