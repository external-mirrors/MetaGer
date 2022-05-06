#!/bin/bash

set -e

COMMAND_ARGS=""

# Loop through all variables
while IFS='=' read -r name value ; do
  if [[ $name == 'K8S_SECRET'* ]]; then
    $value = escape($value);
    if [ -f $name ]; then
        COMMAND_ARGS="${COMMAND_ARGS} --from-file='$value'"
    else
        COMMAND_ARGS="${COMMAND_ARGS} --from-literal='$value'"
    fi
  fi
done < <(env)

# Create/Update the secret
echo "kubectl -n $KUBE_NAMESPACE create secret generic $CI_COMMIT_REF_SLUG $COMMAND_ARGS"

echo "test";

sub escape {
    $_[0] =~ s/([^a-zA-Z0-9_])/\\$1/g;
    return $_[0];
}