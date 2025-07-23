FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Dependências do sistema
RUN apk add --no-cache \
    nginx \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    mysql-client

# Extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    gd \
    opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurações PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Permissões (ajuste para seu usuário/grupo)
RUN chown -R www-data:www-data /var/www/html