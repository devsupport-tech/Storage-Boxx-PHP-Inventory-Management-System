# Storage Boxx - Optimized Production Dockerfile
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    redis-tools \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mbstring \
        xml \
        zip \
        intl \
        opcache \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        openssl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules required for Storage Boxx
RUN a2enmod rewrite headers expires deflate

# Configure Apache for Storage Boxx
COPY docker/apache/storage-boxx.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create required directories and set permissions
RUN mkdir -p /var/www/html/storage/cache \
    /var/www/html/storage/logs \
    /var/www/html/storage/sessions \
    /var/www/html/storage/uploads \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 644 /var/www/html \
    && chmod -R 755 /var/www/html/lib \
    && chmod -R 755 /var/www/html/pages \
    && chmod -R 755 /var/www/html/assets

# Configure PHP for production
COPY docker/php/php.ini /usr/local/etc/php/conf.d/storage-boxx.ini

# Health check
COPY docker/healthcheck.sh /usr/local/bin/healthcheck.sh
RUN chmod +x /usr/local/bin/healthcheck.sh
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD /usr/local/bin/healthcheck.sh

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-ctl", "-D", "FOREGROUND"]