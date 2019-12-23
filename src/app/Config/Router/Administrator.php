<?php

/** @var Phalcon\Mvc\Router $router */
$router
	->add($uriPrefix . '/:controller/:action/:params',
		[
			'controller' => 1,
			'action'     => 2,
			'params'     => 3,
		]
	)
	->setName('admin-base')
	->convert(
		'controller',
		function ($controller) {
			return 'admin_' . $controller;
		}
	);

$router
	->add($uriPrefix . '/:controller/:action/:int/:params',
		[
			'controller' => 1,
			'action'     => 2,
			'id'         => 3,
			'params'     => 4,
		]
	)
	->setName('admin-edit')
	->convert(
		'controller',
		function ($controller) {
			return 'admin_' . $controller;
		}
	);

$router
	->add($uriPrefix . '/media/:action/:params',
		[
			'controller' => 'admin_media',
			'action'     => 1,
			'params'     => 2,
		]
	)
	->setName('media-base');

$router
	->add($uriPrefix . '/raw/media/:action/:params',
		[
			'controller' => 'admin_raw',
			'forward'    => 'admin_media',
			'action'     => 1,
			'params'     => 2,
		]
	)
	->setName('media-raw');

// Ucm Item
$router
	->add($uriPrefix . '/content/([a-z0-9\-]+)/:action/:params',
		[
			'controller' => 'admin_ucm_item',
			'context'    => 1,
			'action'     => 2,
			'params'     => 3,
		]
	)
	->setName('ucm-item-base');

$router
	->add($uriPrefix . '/content/([a-z0-9\-]+)/:action/:int/:params',
		[
			'controller' => 'admin_ucm_item',
			'context'    => 1,
			'action'     => 2,
			'id'         => 3,
			'params'     => 4,
		]
	)
	->setName('ucm-iten-edit');

// Raw Ucm Item
$router
	->add($uriPrefix . '/raw/content/([a-z0-9\-]+)/:action/:params',
		[
			'controller' => 'admin_raw',
			'forward'    => 'admin_ucm_item',
			'context'    => 1,
			'action'     => 2,
			'params'     => 3,
		]
	)
	->setName('raw-ucm-item-base');

$router
	->add($uriPrefix . '/raw/content/([a-z0-9\-]+)/:action/:int/:params',
		[
			'controller' => 'admin_raw',
			'forward'    => 'admin_ucm_item',
			'context'    => 1,
			'action'     => 2,
			'id'         => 3,
			'params'     => 4,
		]
	)
	->setName('raw-ucm-item-edit');

// Ucm comment
$router
	->add($uriPrefix . '/([a-z0-9\-]+)/comment/:action/:params',
		[
			'controller'       => 'admin_ucm_comment',
			'referenceContext' => 1,
			'action'           => 2,
			'params'           => 3,
		]
	)
	->setName('ucm-comment-base');

$router
	->add($uriPrefix . '/([a-z0-9\-]+)/comment/:action/:int/:params',
		[
			'controller'       => 'admin_ucm_comment',
			'referenceContext' => 1,
			'action'           => 2,
			'id'               => 3,
			'params'           => 4,
		]
	)
	->setName('ucm-comment-edit');

// Ucm field
$router
	->add($uriPrefix . '/group-field/([a-z0-9\-]+)/:action/:params',
		[
			'controller' => 'admin_ucm_group_field',
			'context'    => 1,
			'action'     => 2,
			'params'     => 3,
		]
	)
	->setName('ucm-group-field-base');

$router
	->add($uriPrefix . '/group-field/([a-z0-9\-]+)/:action/:int/:params',
		[
			'controller' => 'admin_ucm_group_field',
			'context'    => 1,
			'action'     => 2,
			'id'         => 3,
			'params'     => 4,
		]
	)
	->setName('ucm-group-field-edit');

$router
	->add($uriPrefix . '/field/([a-z0-9\-]+)/:action/:params',
		[
			'controller' => 'admin_ucm_field',
			'context'    => 1,
			'action'     => 2,
			'params'     => 3,
		]
	)
	->setName('ucm-field-base');

$router
	->add($uriPrefix . '/field/([a-z0-9\-]+)/:action/:int/:params',
		[
			'controller' => 'admin_ucm_field',
			'context'    => 1,
			'action'     => 2,
			'id'         => 3,
			'params'     => 4,
		]
	)
	->setName('ucm-field-edit');

// Ajax route
$router->add($uriPrefix . '/request/(get|post)/([a-zA-Z-]+)/:params',
	[
		'controller' => 'request',
		'action'     => 1,
		'callback'   => 2,
		'id'         => 3,
		'params'     => 4,
	]
);

// Plugin route
$router->add($uriPrefix . '/plugin/([a-zA-Z]+)/([a-zA-Z]+)/:params',
	[
		'controller' => 'admin_plugin',
		'action'     => 'edit',
		'group'      => 1,
		'plugin'     => 2,
		'params'     => 3,
	]
);