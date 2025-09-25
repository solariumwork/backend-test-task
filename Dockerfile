FROM php:8.3-cli-alpine AS sio_test
RUN apk add --no-cache git zip bash

# Setup php extensions
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql

# Xdebug
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS linux-headers

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Composer
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Run migration and fixtures
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Pre-commit hooks
COPY scripts/install-hooks.sh /app/scripts/install-hooks.sh
RUN chmod +x /app/scripts/install-hooks.sh

# Setup php app user
ARG USER_ID=1000
RUN adduser -u ${USER_ID} -D -H app
USER app

# Copy project and create psalm cache
COPY --chown=app . /app
RUN mkdir -p /app/var/psalm/cache \
    && chown -R app:app /app/var

WORKDIR /app

EXPOSE 8337

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]