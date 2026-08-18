#!/bin/bash

HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:53}
HELM_RELEASE_NAME=$(echo $HELM_RELEASE_NAME | sed 's/-$//')

# The valkey subchart appends suffixes to its fullname without re-truncating,
# and Kubernetes object names are capped at 63 chars. The longest suffix is
# "-prestop-script" (15 chars, on the ConfigMap carrying the Sentinel preStop
# hook), so the name itself has to fit in 48 — leaving 41 here once "-valkey"
# is appended. Left to its own default the subchart would name itself
# "<release>-valkey" truncated at 63, which overflows the moment that suffix
# lands. chart.valkeyFullname in the main chart's templates must be told the
# same name via valkeyName, or the app's REDIS_* env points at nothing.
VALKEY_RELEASE_NAME="$(echo ${HELM_RELEASE_NAME:0:41} | sed 's/-$//')-valkey"

helm dependency update chart/
helm -n $KUBE_NAMESPACE upgrade --install \
    ${HELM_RELEASE_NAME} \
    chart/ \
    -f $DEPLOYMENT_HELM_VALUES \
    --set environment=$APP_ENV \
    --set ingress.hosts[0].host="$DEPLOYMENT_URL" \
    --set image.fpm.tag=$DOCKER_FPM_IMAGE_TAG \
    --set image.nginx.tag=$DOCKER_NGINX_IMAGE_TAG \
    --set app_url=$APP_URL \
    --set valkeyName=${VALKEY_RELEASE_NAME} \
    --set valkey.fullnameOverride=${VALKEY_RELEASE_NAME} \
    --wait