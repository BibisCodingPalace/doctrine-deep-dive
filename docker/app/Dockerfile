# syntax=docker/dockerfile:1
ARG PHP_VERSION=8.4
ARG FRANKENPHP_VERSION=1

FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION}

# install-php-extensions is preinstalled in the FrankenPHP image.
# See https://github.com/mlocati/docker-php-extension-installer
RUN install-php-extensions \
        @composer \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        zip

ENV SERVER_NAME=":8000"

WORKDIR /app
