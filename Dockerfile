
FROM composer:2.9.8 AS composer-bin

FROM node:24.16.0-bookworm-slim AS node-bin

FROM php:8.4.20-fpm-bookworm

RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev nano \
    ca-certificates libicu-dev procps \
    libjpeg62-turbo-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_pgsql intl zip pcntl sockets gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN ln -sf /usr/share/zoneinfo/Europe/Rome /etc/localtime && dpkg-reconfigure -f noninteractive tzdata

COPY --from=composer-bin /usr/bin/composer /usr/local/bin/composer
COPY --from=node-bin /usr/local/bin/node /usr/local/bin/node
COPY --from=node-bin /usr/local/bin/npm /usr/local/bin/npm
COPY --from=node-bin /usr/local/bin/npx /usr/local/bin/npx
COPY --from=node-bin /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN ln -s /usr/local/lib/node_modules /usr/local/bin/node_modules \
    && ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm-cli.js \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx-cli.js \
    && composer global require laravel/installer

ENV PATH="/root/.composer/vendor/bin:/usr/local/lib/node_modules/npm/bin:${PATH}"

RUN mkdir -p /var/www/html
WORKDIR /var/www/html

RUN rm -rf /var/www/html/*
EXPOSE 9000

CMD ["php-fpm"]
