FROM php:8.2-apache

RUN apt-get update && apt-get install -y libsqlite3-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo_sqlite

# Point Apache at the public/ folder, not the repo root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html

# Ensure the SQLite data folder is writable
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

EXPOSE 80
