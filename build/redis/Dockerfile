FROM redis:6

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint-mg.sh

ENTRYPOINT [ "docker-entrypoint-mg.sh" ]
CMD ["redis-server"]