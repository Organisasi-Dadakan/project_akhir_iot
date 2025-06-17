# --- STAGE 1: Build Aset Frontend ---
FROM node:18-alpine AS node_builder

WORKDIR /var/www/html

# Salin file dependensi dan install
COPY package.json yarn.lock ./
RUN yarn install

# Salin sisa file dan build aset
COPY . .
RUN yarn build

# --- STAGE 2: Build Aplikasi PHP ---
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

# Manfaatkan caching: Salin file composer dulu, baru install
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Salin semua sisa file aplikasi
COPY --chown=www-data:www-data . .

# Salin aset yang sudah di-build dari stage node_builder
COPY --from=node_builder /var/www/html/public/build /var/www/html/public/build

# Atur hak akses yang benar untuk Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Ekspos port dan jalankan PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]