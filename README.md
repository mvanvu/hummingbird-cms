# Hummingbird CMS
The CMS based on Phalcon 4 and UIKit 3

## Requirements
- Apache
- PHP >= 7.2
- MySql >= 5.7
- Phalcon >= 4.0
- PHP ZIP extension
- PHP mod-rewrite
- <a href="https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx" rel="nofollow">Composer</a>

## Clone this repo
```sh
git clone https://github.com/mvanvu/hummingbird-cms.git
cd hummingbird-cms
```

## Build with Docker
```sh
docker-compose build
docker-compose up -d
```

## Composer install
```sh
composer install
```

Rename /src/public/install.php-dist to /src/public/install.php
Browse http://localhost:8000 and enjoy