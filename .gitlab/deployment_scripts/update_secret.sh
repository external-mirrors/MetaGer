#!/bin/bash

set -e

# Create/Update the secret
kubectl -n $KUBE_NAMESPACE create secret generic $CI_COMMIT_REF_SLUG \
  --from-file=${ENV_PRODUCTION} \
  --from-file=${SUMAS} \
  --from-file=${SUMASEN} \
  --dry-run \
  --save-config \
  -o yaml | \
  kubectl apply -f -
