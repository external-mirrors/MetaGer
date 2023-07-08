#!/bin/bash

set -e

_trap() {
  echo "Waiting for child processes to finish"
  php artisan fpm:graceful-stop
  echo "Stopping FPM"
  kill -s SIGQUIT $FPM_PID
}

trap _trap SIGQUIT

validate_laravel

# Production version will have the .env file mounted at /home/metager/.env
if [ -f /home/metager/.env ];
then
  cp /home/metager/.env .env
fi

# Create the useragents table in the sqlite database
php artisan migrate:refresh --force --path=database/migrations/2019_10_15_103139_create_user_agents_table.php

php artisan optimize
php artisan route:clear # Do not cache routes; Interferes with Localization

php artisan spam:load
php artisan load:affiliate-blacklist

docker-php-entrypoint php-fpm &
FPM_PID=$!
wait