# This file is a template, and might need editing before it works on your project.
FROM composer:2 as composer_stage

FROM php:7.3.33-fpm-alpine3.14

# Customize any core extensions here
#RUN apt-get update && apt-get install -y \
#        libfreetype6-dev \
#        libjpeg62-turbo-dev \
#        libmcrypt-dev \
#        libpng12-dev \
#    && docker-php-ext-install -j$(nproc) iconv mcrypt \
#    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
#    && docker-php-ext-install -j$(nproc) gd

RUN apk update
RUN apk add git
RUN git config --global url."https://".insteadOf git://

# Install dev dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    curl-dev \
    imagemagick-dev \
    libtool \
    libxml2-dev
 
# Install production dependencies
RUN apk add --no-cache \
    bash \
    curl \
    g++ \
    gcc \
    git \
    imagemagick \
    libc-dev \
    libpng-dev \
    make \
    yarn \
    openssh-client \
    rsync \
    zlib-dev \
    libzip-dev
 
# Install PECL and PEAR extensions
RUN pecl install \
    imagick \
    xdebug

# Install and enable php extensions
RUN docker-php-ext-enable \
    imagick \
    xdebug
RUN docker-php-ext-configure zip
RUN docker-php-ext-install \
    curl \
    pdo \
    pdo_mysql \
    pcntl \
    xml \
    gd \
    zip \
    bcmath

#COPY config/php.ini /usr/local/etc/php/
#COPY src/ /var/www/html/

CMD mkdir myarchery-api
WORKDIR /myarchery-api
COPY . /myarchery-api
COPY --from=composer_stage /usr/bin/composer /usr/bin/composer
COPY composer.json /myarchery-api 
#/var/www/html/
CMD mkdir log

#RUN php artisan migrate
RUN composer install
RUN rm -rf vendor
RUN composer update -d .

#CMD ["php-fpm"]

EXPOSE 3000
