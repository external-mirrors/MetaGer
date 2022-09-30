#!/bin/bash

set -e

HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:53}
HELM_RELEASE_NAME=$(echo $HELM_RELEASE_NAME | sed 's/-$//')

# Get All existing tags for the fpm repo
echo "Fetching existing fpm tags..."
declare -A existing_tags_fpm
get_tags_url=$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$FPM_REPOSITORY_ID/tags
page=1
counter=1
while [[ "$page" != "" && $counter -le 50 ]]
do
    tags=$(curl --fail --silent -D headers.txt "${get_tags_url}?page=$page" | jq -r ".[][\"name\"]")
    for tag in $tags
    do
        if [[ $tag = ${DOCKER_IMAGE_TAG_PREFIX}-* && "$tag" != $DOCKER_IMAGE_TAG_PREFIX && $tag != $DOCKER_FPM_IMAGE_TAG ]]
        then
            existing_tags_fpm[$tag]=1
        fi
    done
    while read header
    do
        header=$(echo $header | sed -r 's/\s+//g')
        key=$(echo $header | cut -d':' -f1 )
        value=$(echo $header | cut -d':' -f2 )
        case "$key" in
            x-next-page)
                page="$value"
                sleep 1
                ;;
        esac
    done < headers.txt
    counter=$((counter + 1))
done
echo "Got ${#existing_tags_fpm[@]} tags."
echo ""

# Get All existing tags for the nginx repo
echo "Fetching existing nginx tags..."
declare -A existing_tags_nginx
get_tags_url=$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$NGINX_REPOSITORY_ID/tags
page=1
counter=1
while [[ "$page" != "" && $counter -le 50 ]]
do
    tags=$(curl --fail --silent -D headers.txt "${get_tags_url}?page=$page" | jq -r ".[][\"name\"]")
    for tag in $tags
    do
        if [[ $tag = ${DOCKER_IMAGE_TAG_PREFIX}-* && "$tag" != $DOCKER_IMAGE_TAG_PREFIX && $tag != $DOCKER_NGINX_IMAGE_TAG ]]
        then
            existing_tags_nginx[$tag]=1
        fi
    done
    while read header
    do
        header=$(echo $header | sed -r 's/\s+//g')
        key=$(echo $header | cut -d':' -f1 )
        value=$(echo $header | cut -d':' -f2 )
        case "$key" in
            x-next-page)
                page="$value"
                sleep 1
                ;;
        esac
    done < headers.txt
    counter=$((counter + 1))
done
echo "Got ${#existing_tags_nginx[@]} tags."
echo ""

# Get All existing tags for the node repo
echo "Fetching existing Node tags..."
declare -A existing_tags_node
get_tags_url=$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$NODE_REPOSITORY_ID/tags
page=1
counter=1
while [[ "$page" != "" && $counter -le 50 ]]
do
    tags=$(curl --fail --silent -D headers.txt "${get_tags_url}?page=$page" | jq -r ".[][\"name\"]")
    for tag in $tags
    do
        if [[ $tag = ${DOCKER_IMAGE_TAG_PREFIX}-* && "$tag" != $DOCKER_IMAGE_TAG_PREFIX && $tag != $DOCKER_NGINX_IMAGE_TAG ]]
        then
            existing_tags_node[$tag]=1
        fi
    done
    while read header
    do
        header=$(echo $header | sed -r 's/\s+//g')
        key=$(echo $header | cut -d':' -f1 )
        value=$(echo $header | cut -d':' -f2 )
        case "$key" in
            x-next-page)
                page="$value"
                sleep 1
                ;;
        esac
    done < headers.txt
    counter=$((counter + 1))
done
echo "Got ${#existing_tags_node[@]} tags."
echo ""

# Get All existing tags for the redis repo
echo "Fetching existing Redis tags..."
declare -A existing_tags_redis
get_tags_url=$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$REDIS_REPOSITORY_ID/tags
page=1
counter=1
while [[ "$page" != "" && $counter -le 50 ]]
do
    tags=$(curl --fail --silent -D headers.txt "${get_tags_url}?page=$page" | jq -r ".[][\"name\"]")
    for tag in $tags
    do
        if [[ $tag = ${DOCKER_IMAGE_TAG_PREFIX}-* && "$tag" != $DOCKER_IMAGE_TAG_PREFIX && $tag != $DOCKER_REDIS_IMAGE_TAG ]]
        then
            existing_tags_redis[$tag]=1
        fi
    done
    while read header
    do
        header=$(echo $header | sed -r 's/\s+//g')
        key=$(echo $header | cut -d':' -f1 )
        value=$(echo $header | cut -d':' -f2 )
        case "$key" in
            x-next-page)
                page="$value"
                sleep 1
                ;;
        esac
    done < headers.txt
    counter=$((counter + 1))
done
echo "Got ${#existing_tags_redis[@]} tags."
echo ""

# Get List of existing revisions
echo "Fetching Tags from helm revision history to not be deleted..."
declare -A revision_tags_fpm
declare -A revision_tags_nginx
declare -A revision_tags_redis
helm_release_revisions=$(helm -n $KUBE_NAMESPACE history ${HELM_RELEASE_NAME} -o json | jq -r '.[]["revision"]')
for revision in $helm_release_revisions
do
    revision_values=$(helm -n $KUBE_NAMESPACE get values ${HELM_RELEASE_NAME} --revision=$revision -o json | jq -r '.')
    revision_tags_fpm[$(echo $revision_values | jq -r '.image.fpm.tag')]=1
    revision_tags_nginx[$(echo $revision_values | jq -r '.image.nginx.tag')]=1
    revision_tags_redis[$(echo $revision_values | jq -r '.image.redis.tag')]=1
done
echo "Got ${#revision_tags_fpm[@]} tags for fpm."
echo ${!revision_tags_fpm[@]}
echo ""
echo "Got ${#revision_tags_nginx[@]} tags for nginx."
echo ${!revision_tags_nginx[@]}
echo ""
echo "Got ${#revision_tags_redis[@]} tags for redis."
echo ${!revision_tags_redis[@]}
echo ""

# Delete FPM Tags that are in no revision
echo "Deleting unused FPM Tags..."
for fpm_tag in ${!existing_tags_fpm[@]}
do
    if [[ ! -v revision_tags_fpm["$fpm_tag"] ]]
    then
        echo $fpm_tag
        curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$FPM_REPOSITORY_ID/tags/$fpm_tag"
        echo ""
    fi
done
echo ""

# Delete NGINX Tags that are in no revision
echo "Deleting unused NGINX Tags..."
for nginx_tag in ${!existing_tags_nginx[@]}
do
    if [[ ! -v revision_tags_nginx["$nginx_tag"] ]]
    then
        echo $nginx_tag
        curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$NGINX_REPOSITORY_ID/tags/$nginx_tag"
        echo ""
    fi
done

# Delete Redis Tags that are in no revision
echo "Deleting unused Redis Tags..."
for redis_tag in ${!existing_tags_redis[@]}
do
    if [[ ! -v revision_tags_nginx["$redis_tag"] ]]
    then
        echo $redis_tag
        curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$REDIS_REPOSITORY_ID/tags/$redis_tag"
        echo ""
    fi
done

# Delete Node Tags
echo "Deleting unused Node Tags..."
for node_tag in ${!existing_tags_node[@]}
do
    echo $node_tag
    curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$NODE_REPOSITORY_ID/tags/$node_tag"
    echo ""
done
