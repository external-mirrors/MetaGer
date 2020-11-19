#!/bin/sh

# This commands will help initialize data for docker-compose setup
# Its supposed to run in a php docker image

if [ ! -f "/data/.env" ]; then
    cp /data/.env.example /data/.env
fi

if [ ! -f "/config/sumas.json" ]; then
    cp /config/sumas.json.example /config/sumas.json
fi

if [ ! -f "/config/sumasEn.json" ]; then
    cp /config/sumas.json.example /config/sumasEn.json
fi

if [ -f "/data/database/useragents.sqlite" ]; then
    rm /data/database/useragents.sqlite
fi

if [ ! -d "/data/storage/logs/metager" ]; then
    mkdir -p /data/storage/logs/metager
fi

cp /data/database/useragents.sqlite.example /data/database/useragents.sqlite

chmod -R go+w storage bootstrap/cache

docker-php-ext-install pdo pdo_mysql

php artisan wait:db
rm /data/database/useragents.sqlite
touch /data/database/useragents.sqlite
php artisan migrate:fresh
php artisan db:seed