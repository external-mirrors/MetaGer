#!/bin/bash

helm -n $KUBE_NAMESPACE upgrade --install \
    ${HELM_RELEASE_NAME:0:53} \
    chart/ \
    -f $DEPLOYMENT_HELM_VALUES \
    --set environment=$APP_ENV \
    --set ingress.hosts[0].host="$DEPLOYMENT_URL" \
    --set image.fpm.tag=$DOCKER_FPM_IMAGE_TAG \
    --set image.nginx.tag=$DOCKER_NGINX_IMAGE_TAG \
    --set app_url=$APP_URL \
    --wait