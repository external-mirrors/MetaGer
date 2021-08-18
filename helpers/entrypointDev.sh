#!/bin/sh

set -e

/bin/sh -c "/html/helpers/entrypoint.sh"

sed -i 's/^APP_ENV=.*/APP_ENV=local/g' .env; 

php artisan wait:db
rm /html/database/useragents.sqlite
touch /html/database/useragents.sqlite
php artisan migrate:fresh
php artisan db:seed

php-fpm7.4 -F -R