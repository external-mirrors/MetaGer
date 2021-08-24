#!/bin/sh

set -e

mc alias set --path=on --api S3v4 packages $S3_HOST $AWS_ACCESS_KEY_ID $AWS_SECRET_ACCESS_KEY
if mc cp packages/$S3_BUCKETNAME/packages.tar /tmp/; 
then 
    tar -xf /tmp/packages.tar
fi

# Install node modules
npm i --cache .npm --prefer-offline --no-audit --progress=false
npm run prod

# Install composer modules
export COMPOSER_HOME=.composer
composer install --no-dev

# Add the new cache to the bucket
tar -cf /tmp/packages.tar .npm .composer
mc cp /tmp/packages.tar packages/$S3_BUCKETNAME/

# Cleanup
rm /tmp/packages.tar
rm -rf .npm .composer