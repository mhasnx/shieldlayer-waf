FROM php:8.2-apache

# Install required system tools, unzip & CA certificates for SSL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    ca-certificates \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install PDO MySQL & rewrite module
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set Apache document root to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html/
WORKDIR /var/www/html

# Install dependencies & set storage permissions
RUN composer install --no-interaction --no-dev --optimize-autoloader \
    && mkdir -p /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html/storage

EXPOSE 80