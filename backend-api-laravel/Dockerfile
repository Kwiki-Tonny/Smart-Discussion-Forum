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

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy all application files
COPY . /var/www/html

# ============================================================
# FIX: Install dependencies WITHOUT running scripts first,
#      then run the post-autoload-dump script separately.
#      This prevents the "Class 'JsonResponse' not found" error.
# ============================================================
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN composer run-script post-autoload-dump
# Optional: regenerate autoloader to be safe
RUN composer dump-autoload --optimize

# Build frontend assets (Vite)
RUN npm ci && npm run build

# ============================================================
# Cache configuration, routes, and views for performance.
# Clear any stale config first (we're using PostgreSQL, so it's safe).
# ============================================================
RUN php artisan config:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Set proper permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose the port provided by Render
EXPOSE $PORT

# Start the application: run migrations, then serve
CMD php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=$PORT