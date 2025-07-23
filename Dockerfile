# Storage Boxx PHP Application Dockerfile
# This is a symlink/copy of the main Dockerfile for Coolify compatibility

FROM php:8.2-fpm

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    wget \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    redis-tools \
    gosu \
    && rm -rf /var/lib/apt/lists/*

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    opcache \
    xml \
    xmlwriter \
    simplexml

# Configure GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install OpenSSL extension (usually included by default)
RUN docker-php-ext-install openssl || echo "OpenSSL already available"

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf

# Create storage directories
RUN mkdir -p /app/storage/logs \
    && mkdir -p /app/storage/cache \
    && mkdir -p /app/storage/sessions \
    && mkdir -p /app/storage/uploads

# Create entrypoint script to fix permissions at runtime
RUN echo '#!/bin/bash\n\
echo "Fixing permissions..."\n\
# Create all necessary directories\n\
mkdir -p /app/storage/{logs,cache,sessions,uploads}\n\
mkdir -p /app/lib\n\
mkdir -p /app/pages\n\
mkdir -p /app/assets\n\
\n\
# Fix ownership for the entire application\n\
chown -R www-data:www-data /app\n\
\n\
# Set permissions\n\
chmod -R 755 /app\n\
chmod -R 777 /app/storage\n\
\n\
# Create PHP-FPM log directory with proper permissions\n\
mkdir -p /var/log/php-fpm\n\
chown www-data:www-data /var/log/php-fpm\n\
\n\
echo "Permissions fixed. Starting PHP-FPM..."\n\
# Start PHP-FPM with custom configuration\n\
exec php-fpm --nodaemonize --fpm-config /usr/local/etc/php-fpm.conf\n\
' > /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy application code
COPY . /app/

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -v || exit 1

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["/usr/local/bin/docker-entrypoint.sh"]