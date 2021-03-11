<?php

use App\Factory\Factory;

define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/app/Factory/Factory.php';

return Factory::getApplication();