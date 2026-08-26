
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl

# Enable Apache modules
RUN a2enmod rewrite headers expires deflate

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Laravel working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install \
    --optimize-autoloader \
    --no-dev \
    --no-interaction \
    --no-progress

# Create Laravel runtime directories
RUN mkdir -p \
    storage/framework/views \
    storage/framework/cache \
    storage/framework/sessions \
    storage/logs \
    bootstrap/cache

# Set correct permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configure Apache document root for Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf

# Allow Laravel .htaccess rules
RUN printf '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' \
    >> /etc/apache2/apache2.conf

# Suppress Apache ServerName warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Create Laravel storage symlink
RUN php artisan storage:link || true

# Clear build-time Laravel caches
RUN php artisan config:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true

# Create startup script for Render
RUN cat > /usr/local/bin/start.sh <<'EOF'
#!/bin/bash
set -e

echo "Starting Laravel on Render..."

# Render provides the HTTP port through $PORT
if [ -n "$PORT" ]; then
    echo "Using Render PORT: $PORT"

    sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf
    sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf
fi

echo "Preparing Laravel runtime directories..."

mkdir -p \
    storage/framework/views \
    storage/framework/cache \
    storage/framework/sessions \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Clearing Laravel runtime caches..."

php artisan config:clear
php artisan route:clear
php artisan view:clear || true

php artisan storage:link || true

echo "Starting Apache..."

exec apache2-foreground
EOF

RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]

