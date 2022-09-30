#!/bin/bash

set -e

echo $HELM_RELEASE_NAME;
HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:53}
echo $HELM_RELEASE_NAME;
HELM_RELEASE_NAME=$(echo $HELM_RELEASE_NAME | sed 's/-$//')
echo $HELM_RELEASE_NAME;

# Create/Update the secret
kubectl -n $KUBE_NAMESPACE create secret generic ${HELM_RELEASE_NAME} \
  --from-file=${ENV_PRODUCTION} \
  --from-file=${SUMAS} \
  --from-file=${SUMASEN} \
  --from-file=${ADBLACKLIST_DOMAINS} \
  --from-file=${ADBLACKLIST_URL} \
  --from-file=${BLACKLIST_DESCRIPTION_URL} \
  --from-file=${BLACKLIST_DOMAINS} \
  --from-file=${BLACKLIST_URL} \
  --from-file=${USERSEEDER} \
  --dry-run=client \
  --save-config \
  -o yaml | \
  kubectl apply -f -
