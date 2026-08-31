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

if [ ! -f .env ];
then
  cp .env.example .env
fi

sed -i 's/^APP_ENV=.*/APP_ENV=local/g' .env;

# bootstrap/cache is bind-mounted from the host and gitignored, so it survives
# `git checkout` untouched. A route/config/view cache written by an earlier
# `artisan optimize` (or a stint with APP_ENV=production) sits there stale
# across branch switches — Laravel prefers a cached route file whenever one
# exists, regardless of environment, so a checkout with a renamed or new route
# boots with the old route table and no error until something calls route().
php artisan optimize:clear

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