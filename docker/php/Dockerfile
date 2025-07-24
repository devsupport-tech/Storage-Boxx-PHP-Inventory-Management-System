FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

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

# Create storage directories
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/cache \
    && mkdir -p /var/www/html/storage/sessions \
    && mkdir -p /var/www/html/storage/uploads

# Create PHP-FPM pool configuration to fix logging issues
RUN echo '[www]\n\
user = www-data\n\
group = www-data\n\
listen = 9000\n\
listen.owner = www-data\n\
listen.group = www-data\n\
listen.mode = 0660\n\
pm = dynamic\n\
pm.max_children = 5\n\
pm.start_servers = 2\n\
pm.min_spare_servers = 1\n\
pm.max_spare_servers = 3\n\
; Logging configuration\n\
access.log = /proc/self/fd/2\n\
catch_workers_output = yes\n\
decorate_workers_output = no\n\
' > /usr/local/etc/php-fpm.d/www.conf

# Create entrypoint script to fix permissions at runtime
RUN echo '#!/bin/bash\n\
echo "Fixing permissions..."\n\
# Create all necessary directories\n\
mkdir -p /var/www/html/storage/{logs,cache,sessions,uploads}\n\
mkdir -p /var/www/html/lib\n\
mkdir -p /var/www/html/pages\n\
mkdir -p /var/www/html/assets\n\
\n\
# Fix ownership for the entire application\n\
chown -R www-data:www-data /var/www/html\n\
\n\
# Set permissions\n\
chmod -R 755 /var/www/html\n\
chmod -R 777 /var/www/html/storage\n\
\n\
echo "Permissions fixed. Starting PHP-FPM..."\n\
# Start PHP-FPM directly (not as www-data since FPM handles user switching)\n\
exec php-fpm -F\n\
' > /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

# Don't switch to www-data yet - need root for permission fixes

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -v || exit 1

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["/usr/local/bin/docker-entrypoint.sh"]