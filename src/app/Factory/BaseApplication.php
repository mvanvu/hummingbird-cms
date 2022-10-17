<?php

namespace App\Factory;

use App\Helper\Config;
use App\Helper\Router;
use App\Helper\Uri;
use App\Mvc\Model\ModelBinder;
use App\Mvc\View\ViewBase;
use App\Service\Session;
use MaiVu\Php\Registry;
use Phalcon\Encryption\Crypt;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\FactoryDefault\Cli;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Session\Bag;
use Phalcon\Session\Manager;

class BaseApplication
{
	public static function getInstance()
	{
		static $app = null;

		if (null === $app) {
			$di = static::createServiceContainer();

			if (IS_CLI) {
				$app = new FlyApplication($di);
			} elseif (IS_API) {
				$app = new ApiApplication($di);
				$app->setModelBinder(new ModelBinder);
			} else {
				$di->setShared('view', ViewBase::getInstance($di));
				$di->setShared('router', function () {
					// We must use Closure to bind the router
					return Router::getInstance();
				});
				$di->getShared('dispatcher')
					->setModelBinder(new ModelBinder)
					->setEventsManager($di->getShared('eventsManager'));
				$app = new WebApplication($di);
			}
		}

		return $app;
	}

	protected static function createServiceContainer()
	{
		$config   = Factory::getConfig();
		$dbPrefix = $config->get('db.prefix');
		$registry = Registry::create();
		$db       = new Mysql(
			[
				'host'     => $config->get('db.host'),
				'username' => $config->get('db.user'),
				'password' => $config->get('db.pass'),
				'dbname'   => $config->get('db.name'),
				'charset'  => 'utf8mb4',
			]
		);

		if ($extraConfig = $db->fetchColumn('SELECT data FROM ' . $dbPrefix . 'config_data WHERE context = \'cms.config\'')) {
			$registry->merge($extraConfig);
		}

		if (!defined('ADMIN_URI_PREFIX')) {
			define('ADMIN_URI_PREFIX', $registry->get('adminPrefix', 'admin'));
		}

		if (!defined('DEVELOPMENT_MODE')) {
			define('DEVELOPMENT_MODE', $registry->get('development', 'Y') === 'Y');
		}

		if (!DEVELOPMENT_MODE) {
			ini_set('display_errors', false);
			error_reporting(0);
		}

		Config::setDataContext('cms.config', $registry);
		$di = IS_CLI ? new Cli : new FactoryDefault;
		$di->getShared('modelsManager')->setModelPrefix($dbPrefix);
		$di->setShared('config', $config);
		$di->setShared('db', $db);
		$di->setShared('session', function () use ($db, $registry) {
			$adapter = $registry->get('sessionAdapter', 'database');
			$session = new Manager;

			if ('database' === $adapter) {
				$session->setAdapter(Session::getInstance($db));
			} else {
				$session->setAdapter(new Stream);
			}

			$session->start();

			return $session;
		});

		if (IS_CLI) {
			$di->setShared('crypt', new Crypt('aes-256-cfb'));
		} else {
			$di->getShared('flashSession')
				->setAutoescape(false)
				->setCssClasses(
					[
						'error'   => 'uk-alert uk-alert-danger',
						'success' => 'uk-alert uk-alert-success',
						'notice'  => 'uk-alert',
						'warning' => 'uk-alert uk-alert-warning'
					]
				);

			$di->setShared('sessionBag', function () use ($di) {
				return new Bag($di->getShared('session'), Uri::getClient() . '.persistent');
			});
		}

		$di->getShared('crypt')->setKey($config->get('secret.cryptKey'));

		return $di;
	}
}
