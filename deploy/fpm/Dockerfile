FROM php:8.3-fpm

ARG uid
ARG xdebug_enabled

COPY "./deploy/fpm/conf.d/php.ini" "$PHP_INI_DIR/php.ini"
COPY "./deploy/fpm/conf.d/xdebug.ini" "/tmp/conf.d/xdebug.ini"

RUN apt-get update && apt-get install -y \
    curl \
    libjpeg-dev \
    libwebp-dev \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sudo

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd zip opcache sockets intl > /dev/null

RUN if [ "$xdebug_enabled" = "true" ]; then \
        cp /tmp/conf.d/xdebug.ini $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini && \
        pecl install xdebug && \
        docker-php-ext-enable xdebug; \
    fi

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

USER www-data
