FROM php:7.0-apache

RUN apt-get update && \
    apt-get clean

# Copy
COPY tapmi /var/www/html/
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Change mode 777 HTML folder
RUN chmod 777 -R /var/www/html/

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Copy Environment
RUN cp /var/www/html/env-example /var/www/html/.env

# Generate App Key
#RUN php artisan key:generate

RUN docker-php-ext-install mbstring pdo pdo_mysql
RUN chown -R www-data:www-data /var/www/html/
RUN a2enmod rewrite

CMD php artisan serve --host=0.0.0.0 --port=80
EXPOSE 80
