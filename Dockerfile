# syntax = docker/dockerfile:experimental
FROM debian:10 AS dependencies

EXPOSE 8080

# Install System Components
RUN apt update \
    && apt install -y \
    nginx \
    tzdata \
    cron \
    lsb-release \
    apt-transport-https \
    curl \
    zip

# Install PHP Components
RUN curl -o /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list

RUN apt update \
    && apt install -y \
    php7.4 \
    php7.4-xml \
    php7.4-fpm \
    php7.4-common \
    php7.4-curl \
    php7.4-mbstring \
    php7.4-sqlite3 \
    php7.4-mysql \
    php7.4-sqlite \
    php7.4-zip \
    php7.4-redis \
    php7.4-gd \
    php7.4-json \
    php7.4-opcache \
    php7.4-xdebug

# Install Composer
COPY ./helpers/installComposer.sh /usr/bin/installComposer
RUN chmod +x /usr/bin/installComposer && \
    /usr/bin/installComposer && \
    rm /usr/bin/installComposer

# Install Nodejs
COPY ./helpers/installNodejs.sh /usr/bin/installNodejs
RUN chmod +x /usr/bin/installNodejs && \
    /usr/bin/installNodejs && \
    rm /usr/bin/installNodejs
ENV PATH /usr/local/lib/nodejs/bin:$PATH

# Install Minio Client
RUN curl -o /usr/bin/mc "https://dl.min.io/client/mc/release/linux-amd64/mc" &&\
    chmod +x /usr/bin/mc

FROM dependencies AS development

RUN sed -i 's/pid = \/run\/php\/php7.4-fpm.pid/;pid = \/run\/php\/php7.4-fpm.pid/g' /etc/php/7.4/fpm/php-fpm.conf && \
    sed -i 's/error_log = \/var\/log\/php7.4-fpm.log/error_log = \/dev\/stderr/g' /etc/php/7.4/fpm/php-fpm.conf && \
    sed -i 's/;daemonize = yes/daemonize = no/g' /etc/php/7.4/fpm/php-fpm.conf && \
    sed -i 's/listen = \/run\/php\/php7.4-fpm.sock/listen = 9000/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/;request_terminate_timeout = 0/request_terminate_timeout = 30/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/;request_terminate_timeout_track_finished = no/request_terminate_timeout_track_finished = yes/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/;decorate_workers_output = no/decorate_workers_output = no/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/;catch_workers_output = yes/catch_workers_output = yes/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/pm.max_children = 5/pm.max_children = 1024/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/pm.start_servers = 2/pm.start_servers = 50/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 5/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 50/g' /etc/php/7.4/fpm/pool.d/www.conf && \
    sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php/7.4/fpm/php.ini && \
    echo "xdebug.mode = debug" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
    echo "xdebug.start_with_request = yes" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
    echo "xdebug.discover_client_host = true" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
    echo "xdebug.idekey=VSCODE" >> /etc/php/7.4/fpm/conf.d/20-xdebug.ini && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 30M/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 30M/g' /etc/php/7.4/fpm/php.ini && \
    cp /usr/share/zoneinfo/Europe/Berlin /etc/localtime && \
    echo "Europe/Berlin" > /etc/timezone
# (crontab -l ; echo "* * * * * php /html/artisan schedule:run >> /dev/null 2>&1") | crontab - # TODO: Fix crontab

# Using image as non-root
RUN groupadd -g 1000 metager && \
    useradd -b /home/metager -g 1000 -u 1000 -M -s /bin/bash metager
RUN chown -R 1000:1000 /var/lib/nginx /var/log/nginx
RUN mkdir -p /home/metager &&\
    chown 1000:1000 /home/metager
RUN touch /run/nginx.pid && \
    chown 1000:1000 /run/nginx.pid
USER 1000:1000
WORKDIR /html

CMD /html/helpers/entrypointDev.sh

FROM development AS production

USER 0:0

# Opcache configuration
RUN apt purge -y php7.4-xdebug
RUN sed -i 's/expose_php = On/expose_php = Off/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.enable=1/opcache.enable=1/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=128/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.interned_strings_buffer=8/opcache.interned_strings_buffer=8/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=10000/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.max_wasted_percentage=5/opcache.max_wasted_percentage=5/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.validate_timestamps=1/opcache.validate_timestamps=1/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.revalidate_freq=2/opcache.revalidate_freq=300/g' /etc/php/7.4/fpm/php.ini

COPY config/nginx.conf /etc/nginx/nginx.conf
COPY config/nginx-default.conf /etc/nginx/sites-available/default
RUN sed -i 's/fastcgi_pass phpfpm:9000;/fastcgi_pass localhost:9000;/g' /etc/nginx/sites-available/default 

COPY --chown=1000:1000 . /html

RUN chmod +x /html/helpers/*.sh

# Install packages
RUN --mount=type=secret,id=auto-devops-build-secrets . /run/secrets/auto-devops-build-secrets && \
    chmod +x ./helpers/installPackages.sh && \
    /bin/sh -c ./helpers/installPackages.sh

USER 1000:1000

CMD /html/helpers/entrypointProduction.sh

#CMD cp /root/.env .env && \
#    cron -L /dev/stdout && \
