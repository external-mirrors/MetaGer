FROM alpine:3.11.3

RUN apk add --update \
    nginx \
    tzdata \
    ca-certificates \
    dcron \
    zip \
    redis \
    libpng \
    php7 \
    php7-fpm \
    php7-common \
    php7-curl \
    php7-mbstring \
    php7-sqlite3 \
    php7-pdo_mysql \
    php7-pdo_sqlite \
    php7-dom \
    php7-simplexml \
    php7-tokenizer \
    php7-zip \
    php7-redis \
    php7-gd \
    php7-json \
    php7-pcntl \
    php7-opcache \
    php7-fileinfo \
    && rm -rf /var/cache/apk/*

WORKDIR /html

RUN sed -i 's/;error_log = log\/php7\/error.log/error_log = \/dev\/stderr/g' /etc/php7/php-fpm.conf && \
    sed -i 's/;daemonize = yes/daemonize = no/g' /etc/php7/php-fpm.conf && \
    sed -i 's/listen = 127.0.0.1:9000/listen = 9000/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/;request_terminate_timeout = 0/request_terminate_timeout = 30/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/;request_terminate_timeout_track_finished = no/request_terminate_timeout_track_finished = yes/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/;decorate_workers_output = no/decorate_workers_output = no/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/;catch_workers_output = yes/catch_workers_output = yes/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/user = nobody/user = nginx/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/group = nobody/group = nginx/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/pm.max_children = 5/pm.max_children = 1024/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/pm.start_servers = 2/pm.start_servers = 50/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 5/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 25/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/user = www-data/user = nginx/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/group = www-data/group = nginx/g' /etc/php7/php-fpm.d/www.conf && \
    sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php7/php.ini && \
    sed -i 's/expose_php = On/expose_php = Off/g' /etc/php7/php.ini && \
    # Opcache configuration
    sed -i 's/;opcache.enable=1/opcache.enable=1/g' /etc/php7/php.ini && \
    sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=128/g' /etc/php7/php.ini && \
    sed -i 's/;opcache.interned_strings_buffer=8/opcache.interned_strings_buffer=8/g' /etc/php7/php.ini && \
    sed -i 's/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=10000/g' /etc/php7/php.ini && \
    sed -i 's/;opcache.max_wasted_percentage=5/opcache.max_wasted_percentage=5/g' /etc/php7/php.ini && \
    sed -i 's/;opcache.validate_timestamps=1/opcache.validate_timestamps=1/g' /etc/php7/php.ini && \
    sed -i 's/;opcache.revalidate_freq=2/opcache.revalidate_freq=300/g' /etc/php7/php.ini && \
    echo "daemonize yes" >> /etc/redis.conf && \
    ln -s /dev/null /var/log/nginx/access.log && \
    ln -s /dev/stdout /var/log/nginx/error.log && \
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
    chown -R root:nginx storage/logs/metager bootstrap/cache && \
    chmod -R g+w storage/logs/metager bootstrap/cache && \
    crond -L /dev/stdout && \
    php-fpm7
