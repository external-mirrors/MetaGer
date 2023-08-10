FROM node:20 as development

VOLUME [ "/home/node/.npm", "/home/node/metager" ]
RUN mkdir -p /home/node/.npm /home/node/metager && \
    chown -R node:node /home/node/

USER node:node
WORKDIR /home/node/metager

ADD build/node/entrypoint_dev.sh /usr/local/bin/entrypoint
ENTRYPOINT [ "/usr/local/bin/entrypoint" ]

FROM development as production

ADD build/node/entrypoint_production.sh /usr/local/bin/entrypoint

STOPSIGNAL SIGKILL