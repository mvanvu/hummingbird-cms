<?php

namespace App\Helper;

use Phalcon\Loader;

class Constant
{
	const NAMESPACE_HELPER = 'App\\Helper';

	const NAMESPACE_QUEUE = 'App\\Queue';

	const NAMESPACE_MVC = 'App\\Mvc';

	const NAMESPACE_MODEL = 'App\\Mvc\\Model';

	const NAMESPACE_CONTROLLER = 'App\\Mvc\\Controller';

	const NAMESPACE_PLUGIN = 'App\\Plugin';

	const NAMESPACE_WIDGET = 'App\\Widget';

	const NAMESPACE_FIELD = 'App\\Form\\Field';

	const NAMESPACE_RULE = 'App\\Form\\Rule';

	public static function getNamespacePlugin(string $group, string $name)
	{
		static $namespaces = [];
		$namespace = Constant::NAMESPACE_PLUGIN . '\\' . $group . '\\' . $name;

		if (!isset($namespaces[$namespace]))
		{
			$namespaces[$namespace] = PLUGIN_PATH . '/' . $group . '/' . $name;
			(new Loader)->registerNamespaces([Constant::NAMESPACE_PLUGIN . '\\' . $group => $namespaces[$namespace]], true)->register();
		}

		return $namespace;
	}

	public static function getNamespaceWidget(string $name)
	{
		return Constant::NAMESPACE_WIDGET . '\\' . $name;
	}

}