<?php

use MaiVu\Hummingbird\Lib\Factory;

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/app/Library/Factory.php';

// Execute application
Factory::getApplication()->execute();