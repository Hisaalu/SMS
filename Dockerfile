# syntax=docker/dockerfile:1

FROM composer:lts AS deps
WORKDIR /app

RUN --mount=type=bind,source=composer.json,target=composer.json \
    --mount=type=bind,source=composer.lock,target=composer.lock \
    --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-interaction

FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

RUN echo '<Directory /var/www/html/> \n\
    Options Indexes FollowSymLinks \n\
    AllowOverride All \n\
    Require all granted \n\
</Directory>' > /etc/apache2/conf-available/override.conf && \
    a2enconf override

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=deps /app/vendor/ /var/www/html/vendor

COPY . /var/www/html