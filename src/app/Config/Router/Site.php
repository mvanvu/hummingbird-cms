<?php
/** @var Phalcon\Mvc\Router $router */

// Main display
$router
	->addGet($uriPrefix . '/([a-zA-Z0-9_\-\/]+)/:params',
		[
			'controller' => 'display',
			'action'     => 'show',
			'path'       => 1,
			'params'     => 2,
		]
	)
	->setName('show-content');

// Search route
$router->add($uriPrefix . '/search/:params',
	[
		'controller' => 'search',
		'action'     => 'results',
		'params'     => 1,
	]
)->setName('search');

// Ajax route
$router->add($uriPrefix . '/request/(get|post)/([a-zA-Z-]+)/:params',
	[
		'controller' => 'request',
		'action'     => 1,
		'callback'   => 2,
		'params'     => 3,
	]
);

// User route
$router->add($uriPrefix . '/user/(login|logout|account|register|profile)/:params',
	[
		'controller' => 'user',
		'action'     => 1,
		'params'     => 2,
	]
);

$router->addGet($uriPrefix . '/user/forgot',
	[
		'controller' => 'user',
		'action'     => 'forgot',
	]
);


$router->add($uriPrefix . '/user/request',
	[
		'controller' => 'user',
		'action'     => 'request',
	]
);

$router->add($uriPrefix . '/user/(activate|reset)/([0-9a-z]{40})',
	[
		'controller' => 'user',
		'action'     => 1,
		'token'      => 2,
	]
);

// Comment route
$router->addPost($uriPrefix . '/([a-z0-9_\-]+)/comment',
	[
		'controller'       => 'comment',
		'action'           => 'post',
		'referenceContext' => 1,
	]
);

$router->addPost($uriPrefix . '/([a-z0-9_\-]+)/comment/:int',
	[
		'controller'       => 'comment',
		'action'           => 'viewMore',
		'referenceContext' => 1,
		'offset'           => 2,
	]
);