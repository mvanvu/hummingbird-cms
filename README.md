# Hummingbird CMS
The CMS based on Phalcon v4 and UIKit v3

## Requirements
- Apache
- PHP >= 7.2
- MySql >= 5.7
- Phalcon >= 4.0
- PHP ZIP extension
- PHP mod-rewrite
- <a href="https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx" rel="nofollow">Composer</a>

## Included
- <a href="https://github.com/mvanvu/php-registry">Php-registry</a>
- <a href="https://github.com/mvanvu/php-filter">Php-filter</a>
- <a href="https://github.com/mvanvu/php-form">Php-form (customized)</a>

## Core features
- Multilingual
- Categories nested (Nested Set Model)
- Posts
- Comments
- Custom fields
- Menus
- Widgets
- Plugins
- Mailer
- Users
- Roles
- Custom admin path
- Template override
- Auto compress JS and CSS
- ...

## Core assets - a Hurge thank you to
- Jquery v1.12.4
- Jquery ui v1.12.1
- Jquery nested
- UIkit v3.2.6

# Installation for Development
## Clone this repo
```sh
git clone https://github.com/mvanvu/hummingbird-cms.git
```

## Add current user to www-data group (to fix write config INI file during install)
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

Rename /src/public/install.php-dist to /src/public/install.php
Browse http://localhost:8000 and enjoy