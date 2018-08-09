FROM php:7.2.5-fpm

# PHP_CPPFLAGS is used by the docker-php-ext-* scripts (avoid bug during compilation)
ENV PHP_CPPFLAGS="$PHP_CPPFLAGS -std=c++11" \
    COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=prod

WORKDIR /app

# Install packages dependencies
RUN set -ex; \
    \
    apt-get update -q; \
    apt-get install -qy --no-install-recommends \
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
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*;\
    curl -SS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer

# set recommended PHP.ini settings
RUN { \
		echo 'date.timezone = Europe/Paris'; \
        echo 'short_open_tag = off'; \
        echo 'expose_php = off'; \
        echo 'error_log = /proc/self/fd/2'; \
        echo 'opcache.enable = 1'; \
        echo 'opcache.enable_cli = 1'; \
        echo 'opcache.memory_consumption = 256'; \
        echo 'opcache.interned_strings_buffer = 16'; \
        echo 'opcache.max_accelerated_files = 20011'; \
        echo 'opcache.validate_timestamps = 0'; \
        echo 'opcache.fast_shutdown = 1'; \
        echo 'realpath_cache_size = 4096K'; \
        echo 'realpath_cache_ttl = 600'; \
	} > /usr/local/etc/php/php.ini

RUN { \
		echo 'date.timezone = Europe/Paris'; \
        echo 'short_open_tag = off'; \
        echo 'memory_limit = 8192M'; \
	} > /usr/local/etc/php/php-cli.ini

COPY --chown=www-data:www-data . /app

# Set version
ENV APP_VERSION=0.2.1

RUN APP_ENV=prod composer install --optimize-autoloader --no-interaction --no-ansi --no-dev && \
    APP_ENV=prod bin/console cache:clear && \
    APP_ENV=prod bin/console cache:warmup && \
    \
    chown -R www-data:www-data var files && \
    \
    rm -rf docker

CMD ["php-fpm"]
