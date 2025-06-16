# Gunakan image PHP 8.2 FPM Alpine sebagai dasar
FROM php:8.2-fpm-alpine

# Install dependensi sistem yang dibutuhkan, termasuk Node.js dan npm
# Digabungkan menjadi satu layer untuk efisiensi
RUN apk add --no-cache \
    git \
    curl \
    libzip-dev \
    zip \
    supervisor \
    build-base \
    mariadb-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    nodejs \
    npm

# Install ekstensi PHP yang umum digunakan untuk Laravel
RUN docker-php-ext-install pdo pdo_mysql zip exif pcntl gd

# Install Composer (manajer dependensi PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Atur direktori kerja
WORKDIR /var/www/html

# 1. PERUBAHAN UTAMA DIMULAI DARI SINI
# Salin file manifes dependensi terlebih dahulu
# Ini memanfaatkan cache Docker. Layer ini hanya akan di-build ulang jika file-file ini berubah.
COPY composer.json composer.lock package.json package-lock.json ./

# 2. INSTALL DEPENDENSI BACKEND
# Install dependensi PHP tanpa dev-dependencies untuk produksi
RUN composer install --no-dev --optimize-autoloader

# 3. INSTALL DEPENDENSI FRONTEND
RUN npm install

# 4. SALIN SEMUA FILE APLIKASI
# Setelah dependensi terinstall, baru salin sisa kode aplikasi
COPY . .

# 5. BUILD ASET FRONTEND UNTUK PRODUKSI
# Ini akan membuat folder /public/build/manifest.json
RUN npm run build

# 6. BERSIHKAN node_modules UNTUK MENGECILKAN UKURAN IMAGE
# Folder ini tidak lagi dibutuhkan setelah proses build selesai
RUN rm -rf node_modules

# 7. PERBAIKI HAK AKSES
# Pastikan web server (www-data) memiliki izin yang benar untuk folder-folder penting
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Ekspos port yang digunakan oleh PHP-FPM
EXPOSE 9000

# Jalankan PHP-FPM sebagai perintah default
CMD ["php-fpm"]