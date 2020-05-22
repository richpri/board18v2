FROM php:7.4.6-apache

RUN apt-get update && \
    apt-get install -y libzip-dev && \
    rm -rf /var/lib/apt/lists/*

# Setup needed php extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql zip
