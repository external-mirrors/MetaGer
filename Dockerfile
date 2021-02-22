FROM debian:10

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

RUN curl -o /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list

# Install PHP Components
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
    php7.4-opcache

WORKDIR /html

RUN mkdir -p /run/php && \
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
    # Opcache configuration
    sed -i 's/;opcache.enable=1/opcache.enable=1/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=128/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.interned_strings_buffer=8/opcache.interned_strings_buffer=8/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=10000/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.max_wasted_percentage=5/opcache.max_wasted_percentage=5/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.validate_timestamps=1/opcache.validate_timestamps=1/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/;opcache.revalidate_freq=2/opcache.revalidate_freq=300/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 30M/g' /etc/php/7.4/fpm/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 30M/g' /etc/php/7.4/fpm/php.ini && \
    cp /usr/share/zoneinfo/Europe/Berlin /etc/localtime && \
    echo "Europe/Berlin" > /etc/timezone && \
    (crontab -l ; echo "* * * * * php /html/artisan schedule:run >> /dev/null 2>&1") | crontab -

COPY config/nginx.conf /etc/nginx/nginx.conf
COPY config/nginx-default.conf /etc/nginx/conf.d/default.conf
RUN sed -i 's/fastcgi_pass phpfpm:9000;/fastcgi_pass localhost:9000;/g' /etc/nginx/conf.d/default.conf 
COPY --chown=root:nginx . /html

WORKDIR /html
EXPOSE 80

CMD cp /root/.env .env && \
    sed -i 's/^REDIS_PASSWORD=.*/REDIS_PASSWORD=null/g' .env && \
    if [ "$GITLAB_ENVIRONMENT_NAME" = "production" ]; then sed -i 's/^APP_ENV=.*/APP_ENV=production/g' .env; else sed -i 's/^APP_ENV=.*/APP_ENV=development/g' .env; fi && \
    cp database/useragents.sqlite.example database/useragents.sqlite && \
    chown -R root:www-data storage/logs/metager bootstrap/cache && \
    chmod -R g+w storage/logs/metager bootstrap/cache && \
    cron -L /dev/stdout && \
    php artisan spam:load && \
    php-fpm7.4
