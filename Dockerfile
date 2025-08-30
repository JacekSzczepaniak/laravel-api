FROM php:8.3-fpm-alpine

# system deps
RUN apk add --no-cache git bash zlib-dev oniguruma-dev libzip-dev icu-dev g++ make autoconf nginx

# php ext
RUN docker-php-ext-install pdo pdo_mysql opcache intl

# composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# nginx
RUN mkdir -p /run/nginx
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/site.conf /etc/nginx/http.d/default.conf

WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# supervisor-like entrypoint
COPY ./docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080
CMD ["/start.sh"]
