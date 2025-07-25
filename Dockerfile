# Storage Boxx - Simplified Production Dockerfile
FROM php:8.2-apache

# Set environment to avoid interactive prompts
ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies first
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    unzip \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions one by one to isolate issues
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install xml
RUN docker-php-ext-install zip
RUN docker-php-ext-install intl
RUN docker-php-ext-install opcache
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_pgsql

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