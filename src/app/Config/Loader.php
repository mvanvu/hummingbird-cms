<?php

use Phalcon\Loader;

$loader = new Loader;
$loader->registerNamespaces(
	[
		'MaiVu\\Hummingbird\\Plugin' => APP_PATH . '/Plugin',
		'MaiVu\\Hummingbird\\Widget' => APP_PATH . '/Widget',
		'MaiVu\\Hummingbird\\Lib'    => APP_PATH . '/Library',
	]
);

$loader->register();