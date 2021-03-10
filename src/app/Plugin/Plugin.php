<?php

namespace App\Plugin;

use App\Helper\Assets;
use App\Helper\Console;
use App\Helper\Queue;
use App\Helper\Service;
use App\Helper\Uri;
use App\Mvc\View\ViewBase;
use App\Traits\Hooker;
use MaiVu\Php\Registry;
use Phalcon\Loader;

class Plugin
{
	/**
	 * @var Registry
	 */
	protected $config;

	/**
	 * @var Registry
	 */
	protected $params;

	/**
	 * @var boolean
	 */
	protected $isDetached = false;

	use Hooker;

	final public function __construct(Registry $config)
	{
		$this->config = $config;
		$pluginName   = $this->config->get('manifest.name');
		$pluginPath   = PLUGIN_PATH . '/' . $this->config->get('manifest.group') . '/' . $pluginName;
		$pluginView   = $pluginPath . '/app/Tmpl/' . (Uri::isClient('administrator') ? 'Administrator' : 'Site') . '/';

		if (!IS_CLI
			&& is_dir($pluginView)
			&& $view = $this->getRenderer()
		)
		{
			$viewsDir = $view->getViewsDir();
			array_unshift($viewsDir, $pluginView);
			$view->setViewsDir($viewsDir);
		}

		if (is_dir($pluginPath . '/app'))
		{
			(new Loader)
				->registerNamespaces(['App' => $pluginPath . '/app'], true)
				->register();
		}

		$this->callback('onConstruct');
	}

	public function getRenderer(): ViewBase
	{
		return Service::view();
	}

	final public static function addQueue(string $handler, $payload = null, int $priority = Queue::PRIORITY_NORMAL)
	{
		if ($job = Queue::make($handler, $payload, $priority))
		{
			list($group, $name) = explode('\\', str_replace('App\\Plugin\\', '', get_called_class()));

			return Console::getInstance()->executeQueue('plugin:' . $group . '/' . $name . ' --queueJobId=' . $job->queueJobId);
		}

		return false;
	}

	final public function addAssets($assets): Plugin
	{
		Assets::addFromPlugin($assets, $this->config->get('manifest.group'), $this->config->get('manifest.name'));

		return $this;
	}

	public function onRegisterMenus(&$menus)
	{
		if ($menusData = $this->config->get('manifest.menus', []))
		{
			foreach ($menusData as $name => $menusDatum)
			{
				$menus[$name] = $menusDatum;
			}
		}
	}

	public function getConfig(): Registry
	{
		return $this->config;
	}

	public function isDetached(): bool
	{
		return (bool) $this->isDetached;
	}
}
