#!/bin/sh

set -e

/bin/sh -c "/html/helpers/entrypoint.sh"

php artisan spam:load
php artisan load:affiliate-blacklist

php-fpm7.4 -F -R