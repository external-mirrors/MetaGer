FROM php:8.5.9-fpm-trixie AS composer

# Add Composer installation to image
ADD build/fpm/installcomposer.sh /usr/bin/installcomposer
RUN /usr/bin/installcomposer && \
    rm /usr/bin/installcomposer

# UID and GID this image will run under
ARG UID=1000
ARG GID=1000

# Create User
# Using image as non-root
RUN groupadd -g ${GID} metager && \
    useradd -b /home/metager -g ${GID} -u ${UID} -M -s /bin/bash metager && \
    mkdir -p /home/metager &&\
    chown ${UID}:${GID} /home/metager

# Install required php modules
RUN apt update && apt install --no-install-recommends -y \
    procps \
    libzip-dev \
    libpng-dev \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libzstd-dev \
    libpq-dev \
    libmagickwand-dev \
    liblz4-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install zip gd pcntl pdo_mysql pdo_pgsql intl
# igbinary and msgpack are deliberately absent. They were installed and enabled
# but never referenced: nothing in the app, config, php.ini or chart selects
# them as a serializer, and Laravel talks to Redis through predis (pure PHP,
# REDIS_CLIENT is nowhere set and defaults to 'predis'). igbinary also has no
# stable release that builds against PHP 8.5 — 3.2.16 fails on the removed
# ext/standard/php_smart_string.h and only 3.2.17RC1 adds support — so keeping
# them would have meant shipping a release candidate for an unused extension.
RUN yes 'y' | pecl install xdebug-3.5.3 imagick-3.8.1
# phpredis is configured explicitly rather than through `yes 'y'`: that answered
# every prompt affirmatively, including the igbinary and msgpack serializers,
# which now have no headers to build against. The compression backends stay on —
# libzstd-dev and liblz4-dev are installed above for them.
RUN pecl install --configureoptions 'enable-redis-igbinary="no" enable-redis-msgpack="no" enable-redis-lzf="yes" enable-redis-zstd="yes" enable-redis-lz4="yes" with-liblz4="yes"' redis-6.3.0
RUN docker-php-ext-enable redis imagick
RUN docker-php-ext-enable --ini-name=xdebug.ini xdebug

# Add working dir for the code base
RUN mkdir -p /metager/metager_app && \
    chown -R metager:metager /metager
WORKDIR /metager/metager_app

# Add composer Cache Directory
RUN mkdir /composer_cache && \
    chown metager:metager /composer_cache
VOLUME [ "/composer_cache" ]

FROM composer AS development

# Add General Customizations to php.ini
ADD build/fpm/configuration/php/custom.ini $PHP_INI_DIR/conf.d/custom.ini

# Configure PHP (use development configuration as base)
RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
# Add Development Customizations
ADD build/fpm/configuration/php/development.ini $PHP_INI_DIR/conf.d/development.ini

# Configure FPM
ADD build/fpm/configuration/fpm/www_01_development.conf $PHP_INI_DIR/../php-fpm.d/www_01.conf

ADD build/fpm/entrypoint/validate_laravel.sh /usr/local/bin/validate_laravel
ADD build/fpm/entrypoint/entrypoint_development.sh /usr/local/bin/entrypoint

USER metager:metager
ENTRYPOINT [ "entrypoint" ]

FROM development AS production

USER root:root

# Disable xdebug.
#
# opcache used to be installed here as a shared extension. As of PHP 8.5 it is compiled into
# the interpreter (`php -m` lists it in a bare php:8.5-fpm image, and there is no opcache.so),
# so `docker-php-ext-install opcache` configures, builds nothing, and then fails on
# `cp: cannot stat 'modules/*'`. Nothing replaces it: opcache is already loaded, and both
# targets set their own policy — development.ini turns it off, production.ini turns it on and
# tunes it.
RUN rm $PHP_INI_DIR/conf.d/xdebug.ini

# Reconfigure PHP with production values
RUN mv $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
# Remove development php.ini
RUN rm $PHP_INI_DIR/conf.d/development.ini
# Add production php.ini
ADD build/fpm/configuration/php/production.ini $PHP_INI_DIR/conf.d/production.ini
ADD build/fpm/entrypoint/entrypoint_production.sh /usr/local/bin/entrypoint
# Configure FPM for production
ADD build/fpm/configuration/fpm/www_01_production.conf $PHP_INI_DIR/../php-fpm.d/www_01.conf

USER metager:metager

ADD --chown=metager:metager metager /metager/metager_app