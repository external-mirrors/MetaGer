FROM php:8.2.8-fpm-bookworm as composer

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
RUN apt update && apt install -y \
    libzip-dev \
    libpng-dev \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libzstd-dev \
    libpq-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install zip gd pcntl pdo_mysql pdo_pgsql
RUN yes 'y' | pecl install xdebug-3.2.2 igbinary-3.2.14 redis-5.3.7
RUN docker-php-ext-enable igbinary redis
RUN docker-php-ext-enable --ini-name=xdebug.ini xdebug

# Add working dir for the code base
RUN mkdir -p /metager/metager_app && \
    chown -R metager:metager /metager
WORKDIR /metager/metager_app

# Add composer Cache Directory
RUN mkdir /composer_cache && \
    chown metager:metager /composer_cache
VOLUME [ "/composer_cache" ]

FROM composer as development

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

FROM development as production

USER root:root

# Disable xdebug extension and enable opcache
RUN rm $PHP_INI_DIR/conf.d/xdebug.ini && \
    docker-php-ext-install --ini-name=opcache.ini opcache

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