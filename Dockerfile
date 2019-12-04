FROM nginx

RUN apt -y update && apt -y install php-fpm \
    ca-certificates \
    cron \
    zip \
    php7.3-common \
    php7.3-curl \
    php7.3-mbstring \
    php7.3-sqlite3 \
    php7.3-mysql \
    php7.3-xml \
    php7.3-zip \
    php7.3-redis \
    php7.3-gd \
    redis-server

RUN sed -i 's/listen.owner = www-data/listen.owner = nginx/g' /etc/php/7.3/fpm/pool.d/www.conf && \
    sed -i 's/listen.group = www-data/listen.group = nginx/g' /etc/php/7.3/fpm/pool.d/www.conf && \
    sed -i 's/user = www-data/user = nginx/g' /etc/php/7.3/fpm/pool.d/www.conf && \
    sed -i 's/group = www-data/group = nginx/g' /etc/php/7.3/fpm/pool.d/www.conf && \
    sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g' /etc/php/7.3/fpm/php.ini && \
    mkdir /html

# Set correct timezone
RUN ln -fs /usr/share/zoneinfo/Europe/Berlin /etc/localtime && dpkg-reconfigure -f noninteractive tzdata

# Add Cronjob for Laravel
RUN (crontab -l ; echo "* * * * * php /html/artisan schedule:run >> /dev/null 2>&1") | crontab

WORKDIR /html
EXPOSE 80

COPY config/nginx.conf /etc/nginx/nginx.conf
COPY config/nginx-default.conf /etc/nginx/conf.d/default.conf
COPY --chown=1000:nginx . /html
RUN chmod -R g+w storage bootstrap/cache

CMD /etc/init.d/cron start && \
    /etc/init.d/php7.3-fpm start && \
    /etc/init.d/nginx start && \
    /etc/init.d/redis-server start && \
    php artisan requests:fetcher
