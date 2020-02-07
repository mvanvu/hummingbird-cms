FROM ubuntu:18.04

LABEL maintainer="mvanvu@gmail.com" \
description="Hummingbird CMS for dev"

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y sudo \
nano \
wget \
curl \
git-core \
software-properties-common \
libpcre3-dev \
gcc \
make \
re2c \
autoconf

# Apache + php 7.3
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update && apt-get install -y apache2 \
libapache2-mod-php7.3 \
php7.3 \
php7.3-cli \
php7.3-fpm \
php7.3-json \
php7.3-pdo \
php7.3-mysql \
php7.3-zip \
php7.3-gd \
php7.3-mbstring \
php7.3-curl \
php7.3-xml \
php7.3-bcmath \
php7.3-json \
php7.3-psr \
php7.3-dev \
php7.3-apcu

RUN apt-get install locales
RUN locale-gen en_US.UTF-8
ENV TERM xterm

# Apache conf allow .htaccess with RewriteEngine
COPY ./docker/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

VOLUME /var/www/html
EXPOSE 80

RUN chmod -R g+s /var/www
RUN chgrp -R www-data /var/www
RUN find /var/www -type d -exec chmod 755 {} +
RUN find /var/www -type f -exec chmod 644 {} +

WORKDIR /

RUN git clone git://github.com/phalcon/php-zephir-parser.git
WORKDIR php-zephir-parser
RUN phpize && ./configure && make && make install
RUN echo "extension=zephir_parser.so" >> /etc/php/7.3/cli/php.ini

# Phalcon v4
WORKDIR /
RUN wget https://github.com/phalcon/cphalcon/archive/v4.0.3.tar.gz && tar -zxvf v4.0.3.tar.gz
WORKDIR /cphalcon-4.0.3
RUN wget https://github.com/phalcon/zephir/releases/download/0.12.11/zephir.phar && chmod +x zephir.phar
RUN php zephir.phar fullclean && php zephir.phar build

RUN echo "extension=phalcon.so" >> /etc/php/7.3/cli/php.ini
RUN echo "extension=phalcon.so" >> /etc/php/7.3/apache2/php.ini

# RUN install phpunit
WORKDIR /
RUN wget -O phpunit https://phar.phpunit.de/phpunit-8.phar
RUN mv phpunit bin/phpunit
RUN chmod +x bin/phpunit

RUN service apache2 restart
# Start Apache2 on image start
CMD /usr/sbin/apache2ctl -D FOREGROUND