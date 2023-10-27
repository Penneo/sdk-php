FROM composer:2.6.5 AS composer
FROM php:7.2-fpm-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer