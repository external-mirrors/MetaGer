#!/bin/bash

# Call script with KEEP_N variable set to specify the amount of releases to keep

HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:53}
HELM_RELEASE_NAME=$(echo $HELM_RELEASE_NAME | sed 's/-$//')

helm -n $KUBE_NAMESPACE history ${HELM_RELEASE_NAME}
if [ $? -ne 0 ]
then
  echo "Release does not exist yet. Nothing to cleanup!"
  exit 0
fi

set -e

revision_count=$(helm -n $KUBE_NAMESPACE history ${HELM_RELEASE_NAME} -o json | jq -r '. | length')

# Get List of revisions to expire (delete the image tags)
end_index=$(($KEEP_N > $revision_count ? 0 : $revision_count-$KEEP_N))
expired_revisions=$(helm -n $KUBE_NAMESPACE history ${HELM_RELEASE_NAME} -o json | jq -r ".[0:$end_index][][\"revision\"]")

# Loop through those revisions
declare -A expired_fpm_tags
declare -A expired_nginx_tags
declare -A expired_redis_tags
for revision in $expired_revisions
do
    # Get Values for this revision
    revision_values=$(helm -n $KUBE_NAMESPACE get values ${HELM_RELEASE_NAME} --revision=$revision -ojson)
    # Get Image Tags for this revision
    revision_fpm_tag=$(echo $revision_values | jq -r '.image.fpm.tag')
    revision_nginx_tag=$(echo $revision_values | jq -r '.image.nginx.tag')
    revision_redis_tag=$(echo $revision_values | jq -r '.image.redis.tag')

    # Add Tags to the arrays
    if [[ $revision_fpm_tag = ${DOCKER_IMAGE_TAG_PREFIX}-* ]]
    then
        expired_fpm_tags[$revision_fpm_tag]=0
        expired_nginx_tags[$revision_nginx_tag]=0
        expired_redis_tags[$revision_redis_tag]=0
    fi
done

# Delete all gathered fpm tags
for fpm_tag in ${!expired_fpm_tags[@]}
do
    echo "Deleting fpm tag $fpm_tag"
    curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$FPM_REPOSITORY_ID/tags/$fpm_tag"
    echo ""
done
# Delete all gathered nginx tags
for nginx_tag in ${!expired_nginx_tags[@]}
do
    echo "Deleting nginx tag $nginx_tag"
    curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$NGINX_REPOSITORY_ID/tags/$nginx_tag"
    echo ""
done
# Delete all gathered redis tags
for redis_tag in ${!expired_redis_tags[@]}
do
    echo "Deleting redis tag $redis_tag"
    curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$REDIS_REPOSITORY_ID/tags/$redis_tag"
    echo ""
done