FROM php:8.1-apache

# Install PHP extensions required by Nette
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set Apache document root to www directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/temp /var/www/html/log /var/www/html/www/upload \
    && chmod -R 775 /var/www/html/temp /var/www/html/log /var/www/html/www/upload

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies if vendor doesn't exist or is incomplete
RUN if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then \
    composer install --no-interaction --no-dev --optimize-autoloader; \
    fi

EXPOSE 80

CMD ["apache2-foreground"]
