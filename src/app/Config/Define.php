<?php

$scriptDir = trim(dirname($_SERVER['SCRIPT_FILENAME']), '.');
$baseUri   = preg_replace('#/?\?.*$#', '', trim($_SERVER['REQUEST_URI'], '/'));
$rootUri   = preg_replace('#^' . preg_quote(BASE_PATH . '/public', '#') . '#', '', $scriptDir, 1);
define('CMS_VERSION', '1.0.0');
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('BASE_URI', $baseUri);
define('CONFIG_PATH', APP_PATH . '/Config');
define('ROOT_URI', $rootUri);
define('JPATH_ROOT', BASE_PATH);
define('MIGRATIONS_PATH', APP_PATH . '/Migration');
define('LIBRARY_PATH', APP_PATH . '/Library');
define('CACHE_PATH', BASE_PATH . '/cache');
define('MVC_PATH', LIBRARY_PATH . '/Mvc');
define('PLUGIN_PATH', APP_PATH . '/Plugin');
define('WIDGET_PATH', APP_PATH . '/Widget');