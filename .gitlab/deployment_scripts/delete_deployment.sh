#!/bin/bash

HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:53}
HELM_RELEASE_NAME=$(echo $HELM_RELEASE_NAME | sed 's/-$//')

echo "Removing Image Tags..."
.gitlab/deployment_scripts/cleanup_tags_revision.sh
# For some reason an empty image tag gets created for this. We need to delete it until we find out why that is
#'curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$FPM_REPOSITORY_ID/tags/$DOCKER_IMAGE_TAG_PREFIX"'
#'curl --fail --silent -X DELETE -H "JOB-TOKEN: $CI_JOB_TOKEN" "$CI_API_V4_URL/projects/$CI_PROJECT_ID/registry/repositories/$NGINX_REPOSITORY_ID/tags/$DOCKER_IMAGE_TAG_PREFIX"'
echo "Stopping Deployment..."
kubectl -n $KUBE_NAMESPACE delete secret $HELM_RELEASE_NAME
helm -n $KUBE_NAMESPACE delete $HELM_RELEASE_NAME