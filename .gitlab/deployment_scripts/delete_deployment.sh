#!/bin/bash

HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:53}
HELM_RELEASE_NAME=$(echo $HELM_RELEASE_NAME | sed 's/-$//')

# Tear the deployment down first, then the images.
#
# The old order was the other way round, which left a window where the release
# still referenced tags that had just been deleted — harmless for pods already
# running, an ImagePullBackOff for any that restarted in between. There is no
# reason to take that window: nothing about the purge needs the release to exist,
# because it works from the ref prefix rather than from the helm history.
echo "Stopping Deployment..."
kubectl -n $KUBE_NAMESPACE delete secret $HELM_RELEASE_NAME ${HELM_RELEASE_NAME}-redis-sentinel
helm -n $KUBE_NAMESPACE delete $HELM_RELEASE_NAME

echo "Removing Image Tags..."
.gitlab/deployment_scripts/purge_tags.sh
