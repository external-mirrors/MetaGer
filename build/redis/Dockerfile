FROM redis:7.4.2

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint-mg.sh

ENTRYPOINT [ "docker-entrypoint-mg.sh" ]
CMD ["redis-server"]