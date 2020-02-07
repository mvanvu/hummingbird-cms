<?php

namespace MaiVu\Hummingbird\Lib;

use MaiVu\Hummingbird\Lib\Factory;
use MaiVu\Php\Registry;

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
		return Factory::getService('view');
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
