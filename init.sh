#!/bin/sh

# This commands will help initialize data for docker-compose setup
# Its supposed to run in a php docker image
docker-php-ext-install pdo pdo_mysql

if [ ! -f "/data/.env" ]; then
    cp /data/.env.example /data/.env
fi

if [ -f "/data/database/useragents.sqlite" ]; then
    rm /data/database/useragents.sqlite
fi

touch /data/database/useragents.sqlite

chmod -R go+w storage bootstrap/cache

php artisan wait:db
php artisan migrate
php artisan db:seed