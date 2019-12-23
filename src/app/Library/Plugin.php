<?php

namespace MaiVu\Hummingbird\Lib;

use MaiVu\Php\Registry;
use MaiVu\Hummingbird\Lib\Mvc\View\ViewBase;
use ReflectionClass;

class Plugin
{
	/** @var Registry */
	protected $config;

	/** @var Registry */
	protected $params;

	final public function __construct(Registry $config)
	{
		$this->config = $config;
		$this->onConstruct();
	}

	public function onConstruct()
	{

	}

	public function getConfig()
	{
		return $this->config;
	}

	public function getRenderer()
	{
		/** @var ViewBase $view */
		$view            = ViewBase::getInstance();
		$reflectionClass = new ReflectionClass($this);
		$view->setViewsDir(
			[
				TPL_SITE_PATH . '/Tmpl',
				TPL_SITE_PATH,
				dirname($reflectionClass->getFileName()) . '/Tmpl',
				TPL_SYSTEM_PATH,
			]
		);

		$view->disable();

		return $view;
	}

	public function activate()
	{
		// Todo something before activate
	}

	public function deactivate()
	{
		// Todo something before deactivate
	}

	public function uninstall()
	{
		// Todo something before uninstall
	}
}
