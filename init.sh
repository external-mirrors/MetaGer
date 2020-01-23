#!/bin/sh

if [ ! -f "/data/.env" ]; then
    cp /data/.env.example /data/.env
fi

if [ -f "/data/database/useragents.sqlite" ]; then
    rm /data/database/useragents.sqlite
fi
cp /data/database/useragents.sqlite.example /data/database/useragents.sqlite

chmod -R go+w storage bootstrap/cache