FROM php:7-fpm-alpine

RUN apk update && apk add libzip-dev zlib-dev
RUN docker-php-ext-configure zip --with-zlib-dir=/usr/
RUN docker-php-ext-install mysqli pdo pdo_mysql zip
