# BASE STAGE
FROM php:8.0-fpm-alpine as base

RUN apk add --update --no-cache \
    git \
    openssh-client

RUN docker-php-ext-install pdo_mysql sockets

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# BUILD STAGE
FROM base as build

ARG env_file_path
ARG github_composer_auth

WORKDIR /app

COPY . /app
COPY ${env_file_path} /app/.env

RUN composer config -g github-oauth.github.com ${github_composer_auth}
RUN composer install --prefer-dist --no-interaction


# PRODUCTION STAGE
FROM base as production

COPY --from=BUILD /app /app

# DEV STAGE
FROM production as dev

WORKDIR /app

#RUN curl -sS https://get.symfony.com/cli/installer | bash

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY ./docker/php/conf.d/ /usr/local/etc/php/conf.d

CMD ["php-fpm"]

EXPOSE 9000
