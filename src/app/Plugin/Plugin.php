<?php

namespace App\Plugin;

use App\Helper\Assets;
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

	final public function addAssets($assets): Plugin
	{
		settype($assets, 'array');
		$prefix = ROOT_URI . '/resources/' . $this->config->get('manifest.group') . '-' . $this->config->get('manifest.name') . '/public';

		foreach ($assets as &$asset)
		{
			$asset = trim($asset, '/\\\\.');

			if (strpos($asset, $prefix) !== 0)
			{
				$asset = $prefix . '/' . $asset;
			}
		}

		Assets::add($assets);

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
