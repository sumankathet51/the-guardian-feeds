FROM php:8.3-fpm

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    libonig-dev \
    libxml2-dev \
    unzip \
    && docker-php-ext-install \
    mbstring \
    bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN useradd -m -G www-data,root -u 1000 dev

WORKDIR /var/www
COPY composer.json composer.lock ./

RUN composer install --no-scripts --no-autoloader --prefer-dist --optimize-autoloader --no-dev

COPY . .

RUN chown -R dev:www-data /var/www

USER dev

EXPOSE 9000
CMD ["php-fpm"]
