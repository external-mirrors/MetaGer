#!/bin/bash

HELM_RELEASE_NAME=${HELM_RELEASE_NAME:0:53}
HELM_RELEASE_NAME=$(echo $HELM_RELEASE_NAME | sed 's/-$//')

# The redis-sentinel subchart appends up to "-headless" (9 chars) to its
# fullname without re-truncating, and Kubernetes object names are capped at
# 63 chars. Left to its default, the subchart's fullname is
# "<release>-redis-sentinel" (trunc 63), which alone can already hit the cap
# and overflow once "-headless" is appended. Pin it to a shorter, explicit
# name that leaves room for that suffix; chart.redisSentinelFullname in the
# main chart's templates must be told the same name via redisSentinelName.
REDIS_RELEASE_NAME="$(echo ${HELM_RELEASE_NAME:0:50} | sed 's/-$//')-rs"

helm dependency update chart/
helm -n $KUBE_NAMESPACE upgrade --install \
    ${HELM_RELEASE_NAME} \
    chart/ \
    -f $DEPLOYMENT_HELM_VALUES \
    --set environment=$APP_ENV \
    --set ingress.hosts[0].host="$DEPLOYMENT_URL" \
    --set image.fpm.tag=$DOCKER_FPM_IMAGE_TAG \
    --set image.nginx.tag=$DOCKER_NGINX_IMAGE_TAG \
    --set image.redis.tag=$DOCKER_REDIS_IMAGE_TAG \
    --set app_url=$APP_URL \
    --set redisSentinelName=${REDIS_RELEASE_NAME} \
    --set redis-sentinel.fullnameOverride=${REDIS_RELEASE_NAME} \
    --wait