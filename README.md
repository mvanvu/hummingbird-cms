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
- Mailer
- Users
- Roles
- Templates
- Custom admin path
- ...

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

## Prepare to install
- Add 127.0.0.1 hummingbird.local to your hosts (/etc/hosts)
- Rename /src/public/install.php-dist to /src/public/install.php
- Browse http://hummingbird.local:9000/ and enjoy

## Development
Discover the Post plugin https://github.com/mvanvu/hummingbird-cms-post to know how to make a hummingbird plugin