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

if [ ! -f .env ];
then
  cp .env.example .env
fi

sed -i 's/^APP_ENV=.*/APP_ENV=local/g' .env; 

# Make sure App Key is set
php artisan key:generate

php artisan wait:db
php artisan migrate
php artisan db:seed

# Generate helper files to fix inteliphense for laravel project
php artisan ide-helper:generate
php artisan ide-helper:meta

docker-php-entrypoint php-fpm &
FPM_PID=$!
wait