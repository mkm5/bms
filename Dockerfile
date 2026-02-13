FROM php:8.4-fpm

RUN apt-get update && apt-get install -y --no-install-recommends libfcgi-bin

# PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl pdo_pgsql opcache zip

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini "$PHP_INI_DIR/conf.d/app.ini"
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

WORKDIR /app

COPY --chown=www-data:www-data . .

ENV APP_ENV=prod
ENV APP_SECRET=build-time-placeholder

RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

RUN php bin/console cache:clear
RUN php bin/console assets:install public
RUN php bin/console importmap:install
RUN php bin/console tailwind:build --minify
RUN php bin/console asset-map:compile
RUN php bin/console cache:warmup --env=prod

RUN cp -a public /app/public-build
RUN mkdir -p var/storage/default var/storage/documents var/log

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
