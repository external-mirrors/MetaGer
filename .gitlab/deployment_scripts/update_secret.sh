#!/bin/bash

set -e

MAX_HELM_RELEASE_NAME_LENGTH=39
HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:$MAX_HELM_RELEASE_NAME_LENGTH}
HELM_RELEASE_NAME=$(echo "$HELM_RELEASE_NAME" | sed 's/-$//')

# Create/Update the secret
kubectl -n $KUBE_NAMESPACE create secret generic ${HELM_RELEASE_NAME} \
  --from-file=${ENV_PRODUCTION} \
  --from-file=SUMAS_JSON=${SUMAS} \
  --from-file=${SUGGESTIONS} \
  --from-file=${ADBLACKLIST_DOMAINS} \
  --from-file=${ADBLACKLIST_URL} \
  --from-file=${BLACKLIST_DESCRIPTION_URL} \
  --from-file=${BLACKLIST_DOMAINS} \
  --from-file=${BLACKLIST_URL} \
  --dry-run=client \
  --save-config \
  -o yaml | \
  kubectl apply -f -
