# Hummingbird CMS
The CMS based on Phalcon v4 and UIKit v3

## Requirements
- Nginx
- PHP >= 7.2
- MySql >= 5.7.9
- Phalcon >= 4.1.0
- PHP ZIP extension
- PHP mod-rewrite
- <a href="https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx" rel="nofollow">Composer</a>

## Included
- <a href="https://github.com/mvanvu/php-registry">Php-registry</a>
- <a href="https://github.com/mvanvu/php-filter">Php-filter</a>
- <a href="https://github.com/mvanvu/php-form">Php-form</a>

## Core features
- Multilingual
- Universal Content Manager
- Custom fields
- Menus
- Widgets
- Plugins
- Mailer (Thanks phpmailer/phpmailer)
- Users, roles, permissions
- Templates (support assignment for menus)
- Custom admin URL
- ...

## Core applications
- Web application
- Api application
- Cli application
- Socket application 
  + Thanks <a href="https://github.com/swoole/swoole-src">Swoole</a>
  + <a href="https://github.com/mvanvu/hummingbird-cms-chatsample">Official Chat Sample plugin</a>

## Install packages channel
- Support live install plugin from the packages channel
- Default install channel: https://raw.githubusercontent.com/mvanvu/hummingbird-packages/master/packages.json
- Also, support custom packages channel URL from the back-end system configuration

## Core assets
- Mini query js (official)
- UIkit v3.6.16

# Installation for Development
## Clone this repo
```sh
git clone https://github.com/mvanvu/hummingbird-cms.git
```

## Add current user to www-data group (to fix write config file during install)
```sh
sudo usermod -a -G www-data $USER
```

## Chmod permissions
```sh
cd hummingbird-cms
sudo chgrp -R www-data src
sudo chmod -R g+w src
sudo chmod -R g+s src
```

## Composer install
```sh
cd src
composer install
```

## Build with Docker
```sh
cd ../
docker-compose build
docker-compose up -d
```

## Start to install
Browse this URL http://localhost:9000/ and enjoy

## Development
- Discover the Post plugin https://github.com/mvanvu/hummingbird-cms-post to know how to make a hummingbird plugin
- Discover the ChatSample plugin https://github.com/mvanvu/hummingbird-cms-chatsample to know how to make a hummingbird socket plugin