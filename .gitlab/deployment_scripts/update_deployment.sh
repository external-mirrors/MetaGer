#!/bin/bash

helm -n $KUBE_NAMESPACE upgrade --install \
    review-${CI_COMMIT_REF_SLUG} \
    chart/ \
    -f $DEPLOYMENT_HELM_VALUES \
    --set ingress.hosts[0].host=$DEPLOYMENT_URL \
    --set image.fpm.tag=$DOCKER_FPM_IMAGE_TAG \
    --set image.nginx.tag=$DOCKER_NGINX_IMAGE_TAG \
    --set app_url=$APP_URL
    --wait