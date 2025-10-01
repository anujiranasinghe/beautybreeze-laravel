# -------------------------------
# Base PHP + Apache image
# -------------------------------
FROM php:8.2-apache

# -------------------------------
# Install system dependencies
# -------------------------------
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    libcurl4-openssl-dev \
    libpng-dev \
    libonig-dev \
    unzip \
    git \
    curl \
    zip \
    libzip-dev \
    nodejs \
    npm \
 && pecl install mongodb \
 && docker-php-ext-enable mongodb \
 && docker-php-ext-install pdo_mysql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# -------------------------------
# Set working directory
# -------------------------------
WORKDIR /var/www/html

# -------------------------------
# Copy composer binary
# -------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -------------------------------
# Copy Laravel project (inside example-app/)
# -------------------------------
COPY example-app/ .

# -------------------------------
# Install Node.js deps & build assets
# -------------------------------
RUN npm install --legacy-peer-deps && npm run build

# -------------------------------
# Install PHP dependencies
# -------------------------------
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# -------------------------------
# Fix permissions for Laravel
# -------------------------------
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# -------------------------------
# Apache should serve from /public
# -------------------------------
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
 && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf

# -------------------------------
# Expose port 80 (Railway expects this)
# -------------------------------
EXPOSE 80

# -------------------------------
# Start Apache
# -------------------------------
CMD ["apache2-foreground"]
