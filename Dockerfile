# Gunakan image PHP 8.2 FPM Alpine sebagai dasar
FROM php:8.2-fpm-alpine

# Install dependensi sistem yang dibutuhkan
RUN apk add --no-cache \
    git curl libzip-dev zip \
    supervisor build-base mariadb-dev \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    libwebp-dev nodejs npm

# Install ekstensi PHP
RUN docker-php-ext-install pdo pdo_mysql zip exif pcntl gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur direktori kerja
WORKDIR /var/www/html

# --- PERUBAHAN UTAMA DI SINI ---

# 1. SALIN SEMUA FILE APLIKASI TERLEBIH DAHULU
# Ini memastikan file 'artisan' dan semua file lain sudah ada
COPY . .

# 2. BARU INSTALL DEPENDENSI BACKEND
# Sekarang Composer bisa menemukan file 'artisan' untuk menjalankan skripnya
RUN composer install --no-dev --optimize-autoloader

# 3. INSTALL DEPENDENSI FRONTEND & BUILD ASET
RUN npm install
RUN npm run build

# 4. HAPUS node_modules UNTUK MENGECILKAN UKURAN IMAGE
RUN rm -rf node_modules

# 5. PERBAIKI HAK AKSES
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Ekspos port dan jalankan PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]