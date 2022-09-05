<?php

namespace App\Mvc\Controller;

use App\Helper\Event;
use App\Helper\Widget;
use App\Traits\Permission;
use PDO;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Support\Version;

class AdminIndexController extends ControllerBase
{
	/**
	 * @var string
	 */
	public $role = 'manager';

	use Permission;

	public function onConstruct()
	{
		parent::onConstruct();
		$this->view->pick('CPanel/Index');
	}

	public function indexAction()
	{
		/** @var Mysql $db */
		$db           = $this->getDI()->get('db');
		$pdo          = $db->getInternalHandler();
		$prefix       = $this->modelsManager->getModelPrefix();
		$widgetsCount = 0;
		$pluginsCount = 0;

		foreach (Widget::getWidgetItems() as $pos => $widgets)
		{
			$widgetsCount += count($widgets);
		}

		foreach (Event::getPlugins() as $group => $plugins)
		{
			$pluginsCount += count($plugins);
		}

		$this->view->setVars(
			[
				'widgetsCount'    => $widgetsCount,
				'pluginsCount'    => $pluginsCount,
				'phalconVersion'  => Version::get(),
				'cmsVersion'      => CMS_VERSION,
				'phpVersion'      => PHP_VERSION,
				'databaseVersion' => $pdo->getAttribute($pdo::ATTR_SERVER_VERSION),
				'usersCount'      => $db->fetchOne('SELECT COUNT(id) AS num FROM ' . $prefix . 'users') ['num'] ?? 0,
				'mediaCount'      => $db->fetchOne('SELECT COUNT(id) AS num FROM ' . $prefix . 'media')['num'] ?? 0,
				'extensions'      => [
					'Curl'      => extension_loaded('curl'),
					'GetText'   => extension_loaded('gettext'),
					'Gd'        => extension_loaded('gd'),
					'Json'      => extension_loaded('json'),
					'Mbstring'  => extension_loaded('mbstring'),
					'FileInfo'  => extension_loaded('fileinfo'),
					'OpenSSL'   => extension_loaded('openssl'),
					'PDO Mysql' => class_exists('PDO') && in_array('mysql', PDO::getAvailableDrivers()),
				],
			]
		);
	}
}