FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    git curl libzip-dev zip \
    nginx \
    supervisor \
    build-base mariadb-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libwebp-dev

RUN docker-php-ext-install pdo pdo_mysql zip exif pcntl gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN rm -rf /var/cache/apk/*

COPY . .

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]