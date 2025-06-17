# --- STAGE 1: Build Aset Frontend dengan NPM ---
# Menggunakan image Node.js sebagai 'builder' yang akan dibuang nanti.
FROM node:18-alpine AS node_builder

WORKDIR /var/www/html

# Salin file dependensi (termasuk package-lock.json untuk konsistensi).
# Langkah ini di-cache oleh Docker.
COPY package.json package-lock.json ./

# Install dependensi frontend menggunakan npm.
# Langkah ini juga akan di-cache selama package-lock.json tidak berubah.
RUN npm install

# Salin semua sisa file proyek.
COPY . .

# Build aset frontend.
RUN npm run build


# --- STAGE 2: Stage Final untuk Runtime PHP ---
# Menggunakan image PHP-FPM yang ramping sebagai dasar image final.
FROM php:8.2-fpm-alpine

# Install dependensi sistem yang dibutuhkan untuk runtime saja.
RUN apk add --no-cache \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev

# Install ekstensi PHP yang umum untuk Laravel.
RUN docker-php-ext-install pdo pdo_mysql zip exif pcntl gd

# Install Composer.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Manfaatkan caching: Salin file composer dulu, baru install dependensi backend.
COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Salin semua sisa file aplikasi dari direktori lokal Anda.
COPY --chown=www-data:www-data . .

# Salin aset yang sudah jadi dari stage 'node_builder'.
# Hanya hasil build yang disalin, bukan node_modules atau file sumber frontend.
COPY --from=node_builder /var/www/html/public/build /var/www/html/public/build

# Atur hak akses folder yang benar agar dapat ditulis oleh web server.
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Ekspos port dan jalankan PHP-FPM.
EXPOSE 9000
CMD ["php-fpm"]