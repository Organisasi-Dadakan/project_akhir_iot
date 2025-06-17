# --- STAGE 1: Build Aset Frontend dengan NPM ---
FROM node:18-alpine AS node_builder
WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# --- STAGE 2: Stage Final untuk Runtime PHP ---
FROM php:8.2-fpm-alpine

# Install dependensi sistem yang dibutuhkan untuk runtime
RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev

# Install ekstensi PHP
RUN docker-php-ext-install pdo pdo_mysql zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# --- PERUBAHAN UTAMA ADA DI SINI ---

# 1. Salin file composer terlebih dahulu untuk caching
COPY --chown=www-data:www-data composer.json composer.lock ./

# 2. Install dependensi, TAPI lewati (skip) semua skrip
#    Ini mencegah error "artisan not found"
RUN composer install --no-dev --no-scripts --optimize-autoloader

# 3. Sekarang, salin semua sisa file aplikasi (termasuk 'artisan')
COPY --chown=www-data:www-data . .

# 4. Setelah semua file ada, jalankan 'dump-autoload'
#    Perintah ini akan menjalankan skrip yang kita lewati sebelumnya, seperti 'package:discover'
RUN composer dump-autoload --optimize --no-dev

# Salin aset yang sudah di-build dari stage 'node_builder'
COPY --from=node_builder /var/www/html/public/build /var/www/html/public/build

# Atur hak akses folder yang benar
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Ekspos port dan jalankan PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]