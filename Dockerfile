# --- Stage 1: Build & Dependency ---
FROM php:8.2-fpm-alpine AS builder
RUN docker-php-ext-install mysqli

# --- Stage 2: Production Image ---
FROM php:8.2-fpm-alpine
RUN docker-php-ext-install mysqli

# Set working directory di dalam kontainer
WORKDIR /var/www/html

# Salin source code ke dalam kontainer dengan kepemilikan user non-root
COPY --chown=www-data:www-data . /var/www/html

# Keamanan: Jalankan sebagai non-root user
USER www-data

EXPOSE 9000
CMD ["php-fpm"]
