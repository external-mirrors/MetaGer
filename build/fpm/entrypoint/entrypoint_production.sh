#!/bin/bash

set -e

_trap() {
  echo "Waiting for child processes to finish"
  php artisan fpm:graceful-stop
  echo "Stopping FPM"
  kill -s SIGQUIT $FPM_PID
}

# Docker sends SIGQUIT by default (this image's STOPSIGNAL, inherited from the
# php-fpm base image), but Kubernetes always sends SIGTERM to pod containers
# regardless of image metadata. Without trapping SIGTERM too, a pod
# termination reaches this untrapped signal on the bash PID 1, the shell dies
# immediately, and the kernel SIGKILLs the backgrounded php-fpm the instant
# PID 1 exits — skipping fpm:graceful-stop and dropping in-flight requests on
# every rollout.
trap _trap SIGQUIT SIGTERM

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