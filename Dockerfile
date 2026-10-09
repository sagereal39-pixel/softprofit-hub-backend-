FROM php:8.2-apache

# Install PostgreSQL PDO driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache mod_rewrite so .htaccess clean URLs work
RUN a2enmod rewrite

# Allow .htaccess overrides (Apache disables this by default)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Production PHP settings: errors are written to the log, never shown to visitors
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy your project files into Apache's web root
COPY . /var/www/html/

# Render provides the PORT environment variable; Apache needs to listen on it
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
ENV PORT=80
EXPOSE 80

CMD ["apache2-foreground"]