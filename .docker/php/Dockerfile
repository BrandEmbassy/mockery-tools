FROM php:8.1-fpm

RUN apt-get update && apt-get -y install git

RUN curl -sS https://getcomposer.org/installer | php \
&& mv composer.phar /bin/composer

RUN yes | pecl install xdebug-3.3.0 \
    && docker-php-ext-enable xdebug \
    && mkdir -p /var/log/xdebug/profiles \
    && chmod 777 /var/log/xdebug/profiles

COPY ./.docker/php/xdebug.ini $PHP_INI_DIR/conf.d/xdebug.ini

WORKDIR /var/www/html

EXPOSE 9000
