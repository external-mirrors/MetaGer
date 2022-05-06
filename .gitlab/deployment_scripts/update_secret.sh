#!/bin/bash

set -e

COMMAND_ARGS=""

# Loop through all variables
while read key; do
  if [ -f ${!key} ]; then
    COMMAND_ARGS="${COMMAND_ARGS} --from-file=$key=${!key@Q}"
  else
    COMMAND_ARGS="${COMMAND_ARGS} --from-literal=$key=${!key@Q}"
  fi
done < <(compgen -v | grep -i '^K8S_SECRET')

# Create/Update the secret
echo "kubectl -n $KUBE_NAMESPACE create secret generic $CI_COMMIT_REF_SLUG $COMMAND_ARGS"
kubectl -n $KUBE_NAMESPACE create secret generic $CI_COMMIT_REF_SLUG $COMMAND_ARGS
