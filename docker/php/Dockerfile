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

# Create entrypoint script to fix permissions at runtime
RUN echo '#!/bin/bash\n\
# Fix permissions at runtime\n\
mkdir -p /var/www/html/storage/{logs,cache,sessions,uploads}\n\
chown -R www-data:www-data /var/www/html\n\
chmod -R 755 /var/www/html\n\
chmod -R 777 /var/www/html/storage\n\
\n\
# Start PHP-FPM\n\
exec php-fpm\n\
' > /usr/local/bin/docker-entrypoint.sh && chmod +x /usr/local/bin/docker-entrypoint.sh

# Don't switch to www-data yet - need root for permission fixes

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -v || exit 1

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["/usr/local/bin/docker-entrypoint.sh"]