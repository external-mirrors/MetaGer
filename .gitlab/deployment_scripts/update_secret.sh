#!/bin/bash

set -e

# Create/Update the secret
kubectl -n $KUBE_NAMESPACE create secret generic $CI_COMMIT_REF_SLUG \
  --from-file=${ENV_PRODUCTION} \
  --from-file=${SUMAS} \
  --from-file=${SUMASEN} \
  --from-file=${ADBLACKLIST_DOMAINS} \
  --from-file=${ADBLACKLIST_URL} \
  --from-file=${BLACKLIST_DESCRIPTION_URL} \
  --from-file=${BLACKLIST_DOMAINS} \
  --from-file=${BLACKLIST_URL} \
  --from-file=${USERSEEDER} \
  --dry-run \
  --save-config \
  -o yaml | \
  kubectl apply -f -
