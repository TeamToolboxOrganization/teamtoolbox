#syntax=docker/dockerfile:1.4
### BACKEND BUILDER
FROM php:8.2-cli as backend_builder

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update -y && \
  apt-get install -y --no-install-recommends \
  libicu-dev libsqlite3-dev libzip-dev libsodium-dev libpng-dev && \
  \
  docker-php-ext-install -j$(nproc) pdo_sqlite zip sodium gd intl && \
  \
  apt-get remove -y --purge libicu-dev libsqlite3-dev libsodium-dev && \
  apt-get clean && \
  apt-get autoremove -y --purge && \
  rm -rf /var/lib/apt/lists/*

WORKDIR /build

COPY composer.* symfony.lock ./

RUN composer install --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader

### FRONTEND BUILDER
FROM node:20 as frontend_builder

WORKDIR /build

COPY package*.json webpack* ./

RUN npm install

COPY assets assets

RUN npm run build

FROM php:8.2-apache

RUN apt-get update -y && \
  apt-get install -y --no-install-recommends \
  libicu-dev libsqlite3-dev libzip-dev libsodium-dev libpng-dev && \
  \
  docker-php-ext-install -j$(nproc) pdo_sqlite zip sodium gd intl && \
  \
  apt-get remove -y --purge libicu-dev libsqlite3-dev libsodium-dev && \
  apt-get clean && \
  apt-get autoremove -y --purge && \
  rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite
COPY .docker/apache.conf /etc/apache2/sites-enabled/000-default.conf
COPY php.ini /usr/local/etc/php/conf.d/php.ini

COPY . /var/www/html/

COPY --from=frontend_builder /build/public/ /var/www/html/public/
COPY --from=backend_builder /build/vendor /var/www/html/vendor
