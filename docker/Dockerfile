ARG PHP_VERSION
FROM php:${PHP_VERSION}-fpm-alpine

COPY --from=composer:2.6.5 /usr/bin/composer /usr/bin/composer

WORKDIR /app
