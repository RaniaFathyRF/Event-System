# Use official PHP image with required extensions
FROM php:8.2-fpm

# Set working directory inside the container
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    supervisor zip unzip curl libicu-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev git libxml2-dev libzip-dev \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd mbstring zip intl opcache pdo_mysql

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing project files
COPY . /var/www/html


# Set proper permissions for Laravel storage and cache directories
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache 
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/vendor
RUN chmod -R 755 /var/www/html/vendor

# Install Laravel dependencies
RUN composer install --no-dev --prefer-dist

# Create Supervisor configuration directory
RUN mkdir -p /etc/supervisor/conf.d

# Copy Supervisor configuration for Laravel queues
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ensure logs directory exists
RUN mkdir -p /var/log && touch /var/log/laravel-worker.log


# Expose port
EXPOSE 9000

# Start Supervisor
CMD [ "supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]

