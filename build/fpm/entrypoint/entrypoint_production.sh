#!/bin/sh

set -e

validate_laravel

# Production version will have the .env file mounted at /home/metager/.env
if [ -f /home/metager/.env ];
then
  cp /home/metager/.env .env
fi

if [ ! -z $GITLAB_ENVIRONMENT_NAME ];
then
    if [ "$GITLAB_ENVIRONMENT_NAME" = "production" ]; 
    then 
        sed -i 's/^APP_ENV=.*/APP_ENV=production/g' .env; 
    else 
        sed -i 's/^APP_ENV=.*/APP_ENV=development/g' .env; 
    fi
fi

php artisan optimize
php artisan route:trans:cache

php artisan spam:load
php artisan load:affiliate-blacklist

docker-php-entrypoint php-fpm