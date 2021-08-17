#!/bin/sh

/bin/sh -c "/html/helpers/entrypoint.sh"

php artisan wait:db
rm /html/database/useragents.sqlite
touch /html/database/useragents.sqlite
php artisan migrate:fresh
php artisan db:seed