# Hummingbird CMS (ALPHA)

The CMS based on Phalcon v4 and UIKit v3

## Requirements

-   Nginx
-   PHP >= 8.1
-   MySql >= 8
-   Phalcon >= 5.0.3
-   PHP ZIP extension
-   <a href="https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx" rel="nofollow">Composer</a>
-   <a href="https://github.com/swoole/swoole-src">Swoole</a> (optional for the web socket)

## Included

-   <a href="https://github.com/mvanvu/php-registry">Php-registry</a>
-   <a href="https://github.com/mvanvu/php-filter">Php-filter</a>
-   <a href="https://github.com/mvanvu/php-form">Php-form</a>

## Core features

-   Multilingual
-   Universal Content Manager
-   Custom fields
-   Menus
-   Widgets
-   Plugins
-   Mailer (Thanks phpmailer/phpmailer)
-   Users, roles, permissions
-   Templates (support assignment for menus)
-   Custom admin URL
-   ...

## Core applications

-   Web application
-   Api application
-   Fly application (all fly localed at src/app/Console/Fly)
    -   Plugin `php fly plugin:Cms/Backup`: Run a backup under console <a href="https://github.com/mvanvu/hummingbird-cms-backup">Official Backup plugin</a>
    -   QueueJob `php fly queueJob:all`: execute all the queue jobs
    -   Schedule `php fly s:5`: run the task every 5 seconds
    -   Socket `php fly socket host=0.0.0.0 port=2053`: Thanks <a href="https://github.com/swoole/swoole-src">Swoole</a>, <a href="https://github.com/mvanvu/hummingbird-cms-chatsample">Official Chat Sample plugin</a>
    -   Tinker `php fly tinker`: A runtime developer console, interactive debugger and REPL for PHP.

## Install packages channel

-   Support live install plugin from the packages channel
-   Default install channel: https://raw.githubusercontent.com/mvanvu/hummingbird-packages/master/packages.json
-   Also, support custom packages channel URL from the back-end system configuration

## Core assets

-   Mini query js (official)
-   UIkit v3.6.18

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
composer install
```

## Build with the docker

```sh
cd ../
docker-compose build
docker-compose up -d
```

## Run the fly from the docker

```
docker-compose exec ubuntu-22.04 bash
cd /var/www/hummingbird.local
php fly tinker
```

## Start to install

Browse this URL http://localhost:9000/ and enjoy

## Official plugins ([group] - [name]: [URL])

-   Cms - Backup: https://github.com/mvanvu/hummingbird-cms-backup
-   Cms - Post: https://github.com/mvanvu/hummingbird-cms-post
-   Cms - SocialLogin: https://github.com/mvanvu/hummingbird-cms-sociallogin
-   Socket - ChatSample: https://github.com/mvanvu/hummingbird-cms-chatsample
