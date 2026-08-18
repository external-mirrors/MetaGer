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

php artisan wait:db
php artisan migrate --force

# Routes are cached along with everything else now. They could not be before:
# the locale was a route group prefix resolved while routes were *registered*,
# so a warm route cache meant registration never ran and app.locale stayed the
# literal 'default' from config/app.php — which disabled every search engine.
# App\Http\Middleware\ResolveLocale decides the locale per request instead, and
# the route table no longer mentions it.
php artisan optimize

docker-php-entrypoint php-fpm &
FPM_PID=$!
wait