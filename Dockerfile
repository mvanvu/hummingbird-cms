FROM ubuntu:22.04

LABEL maintainer="mvanvu@gmail.com" \
description="Hummingbird CMS for dev"

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y software-properties-common apt-transport-https \
ca-certificates \
lsb-release \
apt-utils \
nano \
wget \
curl \
git \
libpcre3-dev \
build-essential \
gcc \
make \
automake \
re2c \
autoconf

# Php 8.1 + Nginx + Supervisor
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update && apt-get upgrade -y && apt-get install -y nginx \
php8.1 \
php8.1-fpm \
php8.1-common \
php8.1-cli \
php8.1-dev \
php8.1-mysql \
php8.1-xml \
# php8.1-json \ included in PHP 8
php8.1-apcu \
php8.1-curl \
php8.1-mbstring \
php8.1-pdo \
php8.1-zip \
php8.1-gd \
php8.1-bcmath \
supervisor

RUN apt-get install locales
RUN locale-gen en_US.UTF-8
ENV TERM xterm

RUN mkdir -p /var/www/hummingbird.local
RUN chown -R www-data:www-data /var/www/hummingbird.local
RUN chmod -R g+w /var/www/hummingbird.local
RUN chmod -R g+s /var/www/hummingbird.local
RUN service nginx start
RUN service php8.1-fpm start

VOLUME /var/www/hummingbird.local
WORKDIR /

# php-psr extension
RUN git clone https://github.com/jbboehr/php-psr.git
WORKDIR /php-psr
RUN git checkout tags/v1.2.0 ./
RUN phpize && ./configure && make && make test && make install
# RUN echo "extension=psr.so" >> /etc/php/8.1/mods-available/psr.ini
RUN echo "extension=psr.so" >> /etc/php/8.1/cli/php.ini
RUN echo "extension=psr.so" >> /etc/php/8.1/fpm/php.ini
RUN phpenmod psr

# Install Zephir Parser
WORKDIR /
RUN git clone https://github.com/zephir-lang/php-zephir-parser.git
WORKDIR php-zephir-parser
RUN phpize && ./configure && make && make install && make clean && phpize --clean
RUN echo "extension=zephir_parser.so" >> /etc/php/8.1/cli/php.ini
WORKDIR /

# Get zephir
RUN wget https://github.com/zephir-lang/zephir/releases/download/0.16.3/zephir.phar && chmod +x zephir.phar
RUN mv zephir.phar /usr/bin/zephir

# Install Phalcon
RUN git clone https://github.com/phalcon/cphalcon
WORKDIR cphalcon
RUN git checkout tags/v5.0.3 ./
RUN zephir fullclean && zephir compile
WORKDIR ext
RUN phpize ./configure make && make install
RUN echo "extension=phalcon.so" >> /etc/php/8.1/cli/conf.d/50-phalcon.ini
RUN echo "extension=phalcon.so" >> /etc/php/8.1/fpm/conf.d/50-phalcon.ini

# Check the Phalcon module
RUN php -m | grep phalcon

# Install Swoole for WebSocket
WORKDIR /
RUN git clone https://github.com/swoole/swoole-src.git
WORKDIR swoole-src
# RUN git checkout tags/v5.0.0 ./ 
RUN phpize && ./configure --enable-openssl --enable-sockets && make && make install
RUN echo "extension=swoole.so" >> /etc/php/8.1/cli/conf.d/30-swoole.init

WORKDIR /
# Composer
# RUN wget -qO- https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
# chmod +x /usr/local/bin/composer

COPY ./docker/nginx/hummingbird.local.conf /etc/nginx/sites-available/hummingbird.local
RUN ln -s /etc/nginx/sites-available/hummingbird.local /etc/nginx/sites-enabled
RUN nginx -t
RUN service nginx restart
RUN service php8.1-fpm restart
VOLUME /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80
EXPOSE 2053

CMD ["/usr/bin/supervisord", "-n"]