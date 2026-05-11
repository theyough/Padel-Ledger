FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    postgresql-dev \
    zip \
    unzip \
    $PHPIZE_DEPS \
    && docker-php-ext-install intl pdo_pgsql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

COPY . .

EXPOSE 8000

CMD ["sh", "-c", "composer install --no-interaction --prefer-dist --no-progress --no-scripts && php bin/console lexik:jwt:generate-keypair --skip-if-exists && php bin/console doctrine:migrations:migrate --no-interaction && php -S 0.0.0.0:8000 -t public"]
