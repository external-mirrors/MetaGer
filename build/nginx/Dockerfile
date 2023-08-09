FROM nginx:1.25.1 as setup

ARG UID=1000
ARG GID=1000
ARG USER_HOME=/metager

# Create User
RUN groupadd -g ${GID} metager && \
    useradd -b $USER_HOME -g ${GID} -u ${UID} -M -s /bin/bash metager && \
    mkdir -p $USER_HOME && \
    mkdir ${USER_HOME}/nginx_tmp && \
    mkdir ${USER_HOME}/metager_app && \
    chown -R ${UID}:${GID} $USER_HOME

WORKDIR ${USER_HOME}/metager_app

FROM setup as development

ADD build/nginx/configuration/nginx.conf /etc/nginx/nginx.conf
ADD build/nginx/configuration/nginx-default-dev.conf /etc/nginx/conf.d/default.conf

USER metager:metager

FROM development as production

USER root:root
ADD build/nginx/configuration/nginx-default.conf /etc/nginx/conf.d/default.conf
USER metager:metager

ADD metager/public /metager/metager_app/public