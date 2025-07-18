FROM php:8.1-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

# permite super usuário
ENV COMPOSER_ALLOW_SUPERUSER=1

# obtém o Composer usando construção multi-stage (multi-stage build)
# https://docs.docker.com/build/building/multi-stage/
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer

#copiar apenas composer.json e composer.lock (em vez de copiar todo o código fonte)
# https://medium.com/@softius/faster-docker-builds-with-composer-install-b4d2b15d0fff
COPY ./app/composer.* ./

# instalar composer
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# copia os arquivos da aplicação para o atual diretório
COPY ./app .

# rodar o comando "composer dump-autoload --optimize"
RUN composer dump-autoload --optimize