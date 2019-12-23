<?php

use MaiVu\Hummingbird\Lib\Factory;

define('BASE_PATH', dirname(__DIR__));
define('TEST_PHPUNIT_MODE', true);

require_once BASE_PATH . '/app/Library/Factory.php';

Factory::getApplication();