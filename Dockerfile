FROM php:7.2.3-fpm

# PHP_CPPFLAGS is used by the docker-php-ext-* scripts (avoid bug during compilation)
ENV PHP_CPPFLAGS="$PHP_CPPFLAGS -std=c++11" \
    SYMFONY_ENV="prod" \
    SYMFONY_DEBUG=0

# Install packages dependencies
RUN set -ex; \
    \
    apt-get update; \
    apt-get install -y --no-install-recommends \
            zlib1g-dev \
            git \
    ; \
    # Compile ICU (required by intl php extension)
    curl -sS -o /tmp/icu.tar.gz -L http://download.icu-project.org/files/icu4c/59.1/icu4c-59_1-src.tgz; \
    tar -zxf /tmp/icu.tar.gz -C /tmp; \
    cd /tmp/icu/source ; \
    ./configure --prefix=/usr/local; \
    make clean; \
    make ; \
    make install; \
    # Install the PHP extensions
    \
    docker-php-source extract; \
    docker-php-ext-configure intl --with-icu-dir=/usr/local; \
    docker-php-ext-install  -j "$(nproc)" \
            intl \
            pdo \
            pdo_mysql \
            zip \
            bcmath \
    ; \
    pecl install \
            apcu-5.1.8 \
    ; \
    docker-php-ext-enable \
            opcache \
            apcu \
    ; \
    docker-php-source delete; \
    \
    apt-get purge -y --auto-remove; \
    rm -rf /var/lib/apt/lists/*

# Install Composer
RUN set -ex; \
    \
    php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer; \
    chmod +x /usr/local/bin/composer

WORKDIR /var/www/html

# Set php.ini configs
COPY ["./docker/prod/php.ini", "./docker/prod/php_cli.ini", "/usr/local/etc/php/"]

# Install the application
COPY . /var/www/html/

# Remove useless folder
RUN set -ex; \
    \
    rm -R ./docker

RUN set -ex; \
    \
    composer install --no-dev --no-scripts --no-progress --no-suggest --optimize-autoloader; \
    chown -R www-data:www-data /var/www

ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]

CMD ["php-fpm"]
