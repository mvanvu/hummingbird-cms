<?php

namespace App\Helper;

use App\Factory\Factory;
use App\Mvc\View\ViewBase;
use Phalcon\Assets\Manager as AssetManager;
use Phalcon\Crypt;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Router;
use Phalcon\Security;
use Phalcon\Session\Bag as SessionBag;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Url;

class Service
{
	public static function db(): Mysql
	{
		return Factory::getService('db');
	}

	public static function session(): SessionManager
	{
		return Factory::getService('session');
	}

	public static function flashSession(): FlashSession
	{
		return Factory::getService('flashSession');
	}

	public static function sessionBag(): SessionBag
	{
		return Factory::getService('sessionBag');
	}

	public static function security(): Security
	{
		return Factory::getService('security');
	}

	public static function crypt(): Crypt
	{
		return Factory::getService('crypt');
	}

	public static function dispatcher(): Dispatcher
	{
		return Factory::getService('dispatcher');
	}

	public static function router(): Router
	{
		return Factory::getService('router');
	}

	public static function request(): Request
	{
		return Factory::getService('request');
	}

	public static function response(): Response
	{
		return Factory::getService('response');
	}

	public static function view(): ViewBase
	{
		return Factory::getService('view');
	}

	public static function modelsMetadata(): MetaData
	{
		return Factory::getService('modelsMetadata');
	}

	public static function modelsManager(): Manager
	{
		return Factory::getService('modelsManager');
	}

	public static function eventsManager(): EventsManager
	{
		return Factory::getService('eventsManager');
	}

	public static function url(): Url
	{
		return Factory::getService('url');
	}

	public static function assets(): AssetManager
	{
		return Factory::getService('assets');
	}
}