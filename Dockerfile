# This file is a template, and might need editing before it works on your project.
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

RUN apt-get update && apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd

#COPY config/php.ini /usr/local/etc/php/
#COPY src/ /var/www/html/

CMD mkdir myarchery-api
WORKDIR myarchery-api
COPY . /myarchery-api
CMD mkdir log

#RUN php artisan migrate
RUN rm -rf vendor
RUN composer update -d myarchery-api

#CMD [ "php", "./your-script.php" ]
