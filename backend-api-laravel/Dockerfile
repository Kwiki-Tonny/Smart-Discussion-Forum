# Use the official PHP 8.2 FPM image (lightweight and fast)
FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    libpq-dev \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo_pgsql \
        mbstring \
        xml \
        zip \
        simplexml \
        bcmath \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer (latest)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy all application files
COPY . /var/www/html

# Install PHP dependencies (exclude dev, optimize autoloader)
RUN composer install --no-dev --optimize-autoloader

# Build frontend assets (Vite)
RUN npm ci && npm run build

# Generate application key (prevents "no key" error)
RUN php artisan key:generate --force

# Clear any cached config to avoid stale settings
RUN php artisan config:clear && \
    php artisan cache:clear && \
    php artisan view:clear

# Cache config, routes, and views for production performance
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Set correct permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the port that Render provides
EXPOSE $PORT

# Start the application – run migrations and then start the server
CMD php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=$PORT