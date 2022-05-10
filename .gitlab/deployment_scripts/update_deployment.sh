#!/bin/bash

helm -n $KUBE_NAMESPACE upgrade --install \
    $CI_COMMIT_REF_SLUG \
    chart/ \
    -f $DEPLOYMENT_HELM_VALUES \
    --set ingress.hosts[0].host=$DEPLOYMENT_URL