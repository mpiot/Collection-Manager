FROM php:7-fpm

RUN apt-get update && apt-get install -y \
        libicu-dev \
        zlib1g-dev \
        git \
        nodejs \
        npm

RUN pecl install \
        apcu \
        xdebug

RUN docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        zip

RUN docker-php-ext-enable \
        opcache \
        apcu \
        xdebug

# Install composer
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer

# Install minify js and css deps
RUN npm install -g uglify-js
RUN npm install -g uglifycss

COPY php.ini /usr/local/etc/php/

WORKDIR /var/www/html
