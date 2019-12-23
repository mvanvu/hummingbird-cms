<?php

use Phalcon\Mvc\Router;
use MaiVu\Hummingbird\Lib\Helper\Uri;

$uriPrefix = Uri::getBaseUriPrefix();
$client    = Uri::getActive()->getVar('client');

// Create the router
$router = new Router(false);
$router->removeExtraSlashes(true);
$router->setDefaultNamespace('MaiVu\\Hummingbird\\Lib\\Mvc\\Controller');

if (Uri::isHome())
{
	$indexController = $client == 'site' ? 'index' : 'admin_index';
	$router->setDefaultController($indexController);
	$router->setDefaultAction('index');
	$router->add($uriPrefix . '/',
		[
			'controller' => $indexController,
			'action'     => 'index',
		]
	);
}
else
{
	$router->notFound(
		[
			'controller' => 'error',
			'action'     => 'show',
		]
	);
}

include_once __DIR__ . '/Router/' . ucfirst($client) . '.php';

$router->addGet('/assets/(css|js|jsx|img)/([a-zA-Z0-9]+)/([\w.-]+)\.(css|js|jpg|png|gif|jsx)',
	[
		'controller' => 'assets',
		'action'     => 'serve',
		'type'       => 1,
		'assetName'  => 2,
		'fileName'   => 3,
		'fileExt'    => 4,
	]
);

return $router;