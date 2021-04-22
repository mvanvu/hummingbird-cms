<?php

namespace App\Helper;

class Toolbar
{
	protected $toolbars = [];

	public static function add($name, $action, $icon = null)
	{
		static::getInstance()->toolbars[$name] = [
			'route' => $action,
			'icon'  => $icon ?: $name,
			'text'  => Text::_($name),
		];
	}

	public static function getInstance(): Toolbar
	{
		static $instance = null;

		if (null === $instance)
		{
			Assets::add('css/toolbars.css');
			$instance = new static;
		}

		return $instance;
	}

	public static function addCustom($name, $html)
	{
		static::getInstance()->toolbars[$name] = $html;
	}

	public static function render()
	{
		return Service::view()
			->getPartial('Toolbar/Toolbar', ['toolbars' => static::getInstance()->toolbars]);
	}

	public function getItems(): array
	{
		return $this->toolbars;
	}
}