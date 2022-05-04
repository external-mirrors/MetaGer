FROM php:8.1-fpm as composer

# Add Composer installation to image
ADD installcomposer.sh /usr/bin/installcomposer
RUN /usr/bin/installcomposer && \
    rm /usr/bin/installcomposer

FROM composer as development

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
    libpng-dev && \
    docker-php-ext-install zip gd && \
    yes '' | pecl install redis-5.3.7 xdebug-3.1.4 && \
    docker-php-ext-enable redis && \
    docker-php-ext-enable --ini-name=xdebug.ini xdebug

# Add General Customizations to php.ini
ADD configuration/php/custom.ini $PHP_INI_DIR/conf.d/custom.ini

# Configure PHP (use development configuration as base)
RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
# Add Development Customizations
ADD configuration/php/development.ini $PHP_INI_DIR/conf.d/development.ini

# Configure FPM
ADD configuration/fpm/www_01_development.conf $PHP_INI_DIR/../php-fpm.d/www_01.conf

USER metager:metager
ENTRYPOINT [ "/usr/local/sbin/php-fpm" ]

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
ADD configuration/php/production.ini $PHP_INI_DIR/conf.d/production.ini

USER metager:metager