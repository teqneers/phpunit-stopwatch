# Runtime image for testing the library against a specific PHP version.
# The project itself is bind-mounted at /app at run time (see docker-compose.yml),
# so this image only needs to provide PHP + Composer + what Composer relies on.
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

# git and unzip let Composer fetch and extract packages.
RUN apt-get update \
    && apt-get install --yes --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# Composer, copied from the official image (v2 line).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_CACHE_DIR=/tmp/composer-cache

WORKDIR /app

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["vendor/bin/phpunit", "--cache-directory=/tmp/.phpunit-cache", "--display-warnings", "--display-notices"]
