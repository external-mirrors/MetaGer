#!/bin/sh

if [ ! -f .env ];
then
  cp .env.example .env
  php artisan key:generate
fi

if [ "$GITLAB_ENVIRONMENT_NAME" = "production" ]; 
then 
    sed -i 's/^APP_ENV=.*/APP_ENV=production/g' .env; 
else 
    sed -i 's/^APP_ENV=.*/APP_ENV=development/g' .env; 
fi

if [ ! -f "/html/config/sumas.json" ]; then
    cp /html/config/sumas.json.example /html/config/sumas.json
fi

if [ ! -f "/html/config/sumasEn.json" ]; then
    cp /html/config/sumas.json.example /html/config/sumasEn.json
fi

if [ ! -f "/html/database/useragents.sqlite" ]; then
    cp /html/database/useragents.sqlite.example /html/database/useragents.sqlite
fi

if [ ! -d "/html/storage/logs/metager" ]; then
    mkdir -p /html/storage/logs/metager
fi

php artisan optimize

php-fpm7.4 -F -R