FROM ubuntu:18.04

LABEL maintainer="mvanvu@gmail.com" \
description="Hummingbird CMS for dev"

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y sudo \
apt-utils \
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

# Php 7.4 + Nginx + Supervisor
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update && apt-get install -y nginx \
php7.4 \
php7.4-fpm \
php7.4-common \
php7.4-cli \
php7.4-dev \
php7.4-mysql \
php7.4-xml \
php7.4-json \
php7.4-apcu \
php7.4-curl \
php7.4-mbstring \
php7.4-pdo \
php7.4-zip \
php7.4-gd \
php7.4-bcmath \
supervisor

RUN apt-get install locales
RUN locale-gen en_US.UTF-8
ENV TERM xterm

RUN mkdir -p /var/www/hummingbird.local
RUN chown -R www-data:www-data /var/www/hummingbird.local
RUN chmod -R g+w /var/www/hummingbird.local
RUN chmod -R g+s /var/www/hummingbird.local
RUN service nginx start
RUN service php7.4-fpm start

VOLUME /var/www/hummingbird.local
WORKDIR /

# php-psr extension
RUN git clone https://github.com/jbboehr/php-psr.git
WORKDIR /php-psr
RUN phpize && ./configure && make && make test && make install
RUN echo "extension=psr.so" >> /etc/php/7.4/mods-available/psr.ini
RUN phpenmod psr

WORKDIR /

# Phalcon v4
# RUN curl -s https://packagecloud.io/install/repositories/phalcon/stable/script.deb.sh | sudo bash
# RUN apt-get install -y php7.4-phalcon
RUN git clone git://github.com/zephir-lang/php-zephir-parser.git
WORKDIR php-zephir-parser
RUN phpize && ./configure && make && make install
RUN echo "extension=zephir_parser.so" >> /etc/php/7.4/cli/php.ini

WORKDIR /
RUN wget https://github.com/phalcon/cphalcon/archive/v4.1.2.tar.gz && tar -zxvf v4.1.2.tar.gz
WORKDIR /cphalcon-4.1.2

RUN wget https://github.com/zephir-lang/zephir/releases/download/0.12.20/zephir.phar && chmod +x zephir.phar
RUN php zephir.phar fullclean && php zephir.phar build

# RUN echo "extension=phalcon.so" >> /etc/php/7.4/cli/php.ini
RUN echo "extension=phalcon.so" >> /etc/php/7.4/fpm/php.ini

# Install Swoole for WebSocket
WORKDIR /
RUN wget https://github.com/swoole/swoole-src/archive/v4.6.3.tar.gz && tar -zxvf v4.6.3.tar.gz
WORKDIR swoole-src-4.6.3
RUN phpize && ./configure --enable-openssl --enable-sockets && make && make install
# RUN echo "extension=swoole.so" >> /etc/php/7.4/cli/php.ini
RUN echo "extension=swoole.so" >> /etc/php/7.4/fpm/php.ini

WORKDIR /
# Composer
# RUN wget -qO- https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
#    chmod +x /usr/local/bin/composer

COPY ./docker/nginx/hummingbird.local.conf /etc/nginx/sites-available/hummingbird.local
RUN ln -s /etc/nginx/sites-available/hummingbird.local /etc/nginx/sites-enabled
RUN nginx -t
RUN service nginx restart
RUN service php7.4-fpm restart
VOLUME /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80
EXPOSE 2053

CMD ["/usr/bin/supervisord", "-n"]
# CMD /etc/init.d/php7.4-fpm restart && nginx -g "daemon off;"