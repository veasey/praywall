FROM php:8.2-apache

# Install PDO + MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working dir
WORKDIR /var/www/

# Copy app files
COPY . /var/www/

# Set DocumentRoot to /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/public/html|DocumentRoot /var/www/public|' /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

EXPOSE 80
