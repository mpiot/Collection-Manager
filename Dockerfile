FROM php:7-fpm

RUN apt-get update && \
	apt-get install -y \
        libicu-dev \
        zlib1g-dev \
        nodejs \
        npm

RUN rm -rf /var/lib/apt/lists/*

RUN pecl install apcu
RUN docker-php-ext-install intl pdo pdo_mysql zip
RUN docker-php-ext-enable opcache apcu
RUN npm install -g uglify-js uglifycss

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.max_wasted_percentage=10'; \
        echo 'opcache.validate_timestamps=1'; \
#        echo 'opcache.interned_strings_buffer=8'; \
#        echo 'opcache.revalidate_freq=2'; \
#        echo 'opcache.fast_shutdown=1'; \
#	    echo 'opcache.enable_cli=1'; \
	} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# set params specific to Symfony
RUN { \
		echo 'date.timezone = Europe/Paris'; \
		echo 'short_open_tag = off'; \
	} > /usr/local/etc/php/conf.d/symfony.ini

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

CMD ["php-fpm"]
