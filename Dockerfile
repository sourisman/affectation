# =============================================================================
#  AFFECTA — image applicative (PHP 8.3 FPM + extensions + OPcache)
# =============================================================================
FROM php:8.3-fpm-alpine AS base

# Dépendances système nécessaires à la compilation des extensions
RUN apk add --no-cache \
        icu-dev \
        oniguruma-dev \
        libzip-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        curl-dev \
        mysql-client \
        bash \
        tzdata

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        intl \
        gd \
        zip \
        opcache \
        bcmath \
        exif

# OPcache réglé pour la production (valide les fichiers au déploiement, pas à chaque requête)
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=0'; \
        echo 'opcache.memory_consumption=192'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.save_comments=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Configuration PHP durcie
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-affecta.ini

# Composer (autoloader PSR-4 ; l'application démarre aussi sans lui)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances PHP en premier pour profiter du cache de couches
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist \
    || composer update --no-dev --no-interaction --no-scripts

# Code applicatif
COPY . .

RUN mkdir -p storage/logs storage/cache storage/sessions storage/exports public/uploads \
    && chown -R www-data:www-data storage public/uploads \
    && chmod -R 775 storage public/uploads

# Healthcheck HTTP (une requête 200 sur /robots.txt suffit à valider FPM)
HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r "exit(false === @file_get_contents('http://127.0.0.1/robots.txt') ? 1 : 0);" || exit 1

EXPOSE 9000

CMD ["php-fpm"]
