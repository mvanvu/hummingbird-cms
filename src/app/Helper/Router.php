<?php

namespace App\Helper;

use Phalcon\Mvc\Router as PhalconRouter;

class Router
{
	public static function getInstance()
	{
		static $router = null;

		if (null === $router)
		{
			$router = new PhalconRouter(false);
			$router->removeExtraSlashes(true);
			$router->setDefaultNamespace(Constant::NAMESPACE_CONTROLLER);
			$uriPrefix = Uri::getBaseUriPrefix();
			$isAdmin   = Uri::isClient('administrator');

			if (Uri::isHome())
			{
				$indexController = $isAdmin ? 'admin_index' : 'index';
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
						'controller' => $isAdmin ? 'admin_error' : 'error',
						'action'     => 'show',
					]
				);
			}

			if ($isAdmin)
			{
				static::adminRoutes($router, $uriPrefix);
			}
			else
			{
				static::siteRoutes($router, $uriPrefix);
			}

			$router->addPost($uriPrefix . '/file/upload', 'FileSystem::upload');
			$router->add($uriPrefix . '/public/storage/([A-Z0-9\/+=]+)',
				[
					'controller' => 'file_system',
					'action'     => 'handle',
					'key'        => 1,
				],
				['GET', 'DELETE']
			);
			Event::trigger('onInitRouter', [$router], ['Cms']);
		}

		return $router;
	}

	protected static function adminRoutes(PhalconRouter $router, string $uriPrefix)
	{
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
					'controller' => 'admin_media',
					'format'     => 'raw',
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
			->setName('ucm-item-edit');

		// Raw Ucm Item
		$router
			->add($uriPrefix . '/raw/content/([a-z0-9\-]+)/:action/:params',
				[
					'controller' => 'admin_ucm_item',
					'format'     => 'raw',
					'context'    => 1,
					'action'     => 2,
					'params'     => 3,
				]
			)
			->setName('raw-ucm-item-base');

		$router
			->add($uriPrefix . '/raw/content/([a-z0-9\-]+)/:action/:int/:params',
				[
					'controller' => 'admin_ucm_item',
					'format'     => 'raw',
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

		// Widget route
		$router->add($uriPrefix . '/widget/{position}/{name}/:int',
			[
				'controller' => 'admin_widget',
				'action'     => 'handleWidget',
				'position'   => 1,
				'name'       => 2,
				'id'         => 3,
			]
		);
	}

	protected static function siteRoutes(PhalconRouter $router, string $uriPrefix)
	{
		$router->add($uriPrefix . '/([a-zA-Z0-9_\-\/]+)/:params',
			[
				'controller' => 'display',
				'action'     => 'show',
				'path'       => 1,
				'params'     => 2,
			]
		)->setName('show-content');

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
		)->setName('ajax');

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

		$router->add($uriPrefix . '/user/(activate|reset)/([0-9a-z_]{40,44})',
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
	}
}