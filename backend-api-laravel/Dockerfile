# Stage 1: Build Composer Dependencies
FROM composer:latest AS composer

# Copy only composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies (without dev packages for production)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Stage 2: PHP Runtime with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql bcmath

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy Composer dependencies from stage 1
COPY --from=composer /app/vendor /var/www/html/vendor

# Copy application source
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Set permissions for Laravel storage and bootstrap
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure Apache to serve from public directory
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Enable .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/html\/public>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Expose port 80 (Apache default)
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]