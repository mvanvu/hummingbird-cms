<?php

namespace App\Helper;

class Toolbar
{
	protected static $toolbars = [];

	public static function add($name, $action, $icon = null)
	{
		static::$toolbars[$name] = [
			'route' => $action,
			'icon'  => $icon ?: $name,
			'text'  => Text::_($name),
		];
	}

	public static function addCustom($name, $html)
	{
		static::$toolbars[$name] = $html;
	}

	public static function render()
	{
		return Service::view()->getPartial('Toolbar/Toolbar', ['toolbars' => static::$toolbars]);
	}
}