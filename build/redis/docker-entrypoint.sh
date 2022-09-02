#!/bin/bash

_term() {
    echo -n "Waiting for clients to disconnect before stopping"
    while [ "$(redis-cli info clients | grep "connected_clients" | cut -d ":" -f 2 | tr -dc '0-9')" -gt 1 ];
    do
      echo -n "."
      sleep 1;
    done
    echo ""
    echo "Stopping Redis Server with PID $REDIS_PID"
    kill -s SIGKILL $REDIS_PID
    exit 1
}
trap _term SIGTERM

echo "Starting Redis Server"
docker-entrypoint.sh "$@" &
REDIS_PID=$!
wait