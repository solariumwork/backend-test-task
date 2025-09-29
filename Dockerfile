FROM php:8.3-cli-alpine AS sio_test
RUN apk add --no-cache git zip bash postgresql-client

# Setup php extensions
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql bcmath

# Xdebug
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && apk del $PHPIZE_DEPS linux-headers

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Composer
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup php app user
ARG USER_ID=1000
RUN adduser -u ${USER_ID} -D -H app

# Psalm
RUN mkdir -p /app/var/psalm/cache \
    && chown -R app:app /app/var

# Pre-commit hooks
COPY scripts/install-hooks.sh /app/scripts/install-hooks.sh
RUN chmod +x /app/scripts/install-hooks.sh

USER app

COPY --chown=app . /app

WORKDIR /app

EXPOSE 8337

CMD ["php", "-S", "0.0.0.0:8337", "-t", "public", "public/index.php"]