<?php

namespace App\Helper;

use App\Mvc\Model\Plugin as PluginModel;
use App\Plugin\Plugin;
use MaiVu\Php\Registry;

class Event
{
	/**
	 * @var array
	 */
	protected static $handlers = [];

	/**
	 * @var array
	 */
	protected static $plugins = null;

	public static function trigger(string $eventName, array $arguments = [], $groups = null)
	{
		$results = [];
		$plugins = static::getPlugins();

		if (null === $groups)
		{
			$groups = array_keys($plugins);
		}
		elseif (is_string($groups))
		{
			$groups = [$groups];
		}

		foreach ($groups as $group)
		{
			if (isset($plugins[$group]))
			{
				/**
				 * @var  string      $class
				 * @var  PluginModel $config
				 * @var  Plugin      $handler
				 */
				foreach ($plugins[$group] as $name => $plugin)
				{
					$handler = static::getHandler($plugin);

					if ($handler instanceof Plugin)
					{
						if ($handler->isDetached())
						{
							unset(static::$plugins[$group][$name]);
						}
						else
						{
							$results[] = $handler->callback($eventName, $arguments);
						}
					}
				}
			}
		}

		return $results;
	}

	public static function getPlugins()
	{
		if (null === static::$plugins)
		{
			foreach (PluginModel::find(['active = \'Y\'', 'order' => 'ordering ASC']) as $entity)
			{
				static::loadPluginLanguage($entity->group, $entity->name);
				static::$plugins[$entity->group][$entity->name] = $entity;
				Widget::setExtraPath(PLUGIN_PATH . '/' . $entity->group . '/' . $entity->name . '/Widget');
			}
		}

		return static::$plugins;
	}

	public static function loadPluginLanguage(string $group, string $plugin)
	{
		$langCode = Language::getActiveCode();
		$langFile = PLUGIN_PATH . '/' . $group . '/' . $plugin . '/app/Language/' . $langCode . '.php';

		if (is_file($langFile) && ($content = include $langFile))
		{
			Language::load($content, $langCode);
		}
	}

	public static function getHandler(PluginModel $plugin): ?Plugin
	{
		$class = Constant::getNamespacePlugin($plugin->group, $plugin->name);

		if (!array_key_exists($class, static::$handlers))
		{
			if (class_exists($class))
			{
				static::$handlers[$class] = new $class(static::createConfig($plugin));
			}
			else
			{
				static::$handlers[$class] = null;
			}
		}

		return static::$handlers[$class];
	}

	public static function createConfig(PluginModel $plugin): Registry
	{
		return Registry::create(
			[
				'manifest' => Registry::parseData($plugin->manifest),
				'params'   => Registry::parseData($plugin->params),
			]
		);
	}

	public static function getHandlerByGroupName(string $group, string $name): ?Plugin
	{
		return static::$handlers[Constant::getNamespacePlugin($group, $name)] ?? null;
	}

	public static function getHandlerByClass(string $class): ?Plugin
	{
		return static::$handlers[$class] ?? null;
	}

	public static function getPlugin(string $group, string $name): ?PluginModel
	{
		return static::getPlugins()[$group][$name] ?? null;
	}

	public static function getGroup(string $group)
	{
		return static::getPlugins()[$group] ?? [];
	}

	public static function exists(string $group, string $name = null): bool
	{
		$plugins = static::getPlugins();

		if (isset($plugins[$group]))
		{
			return null === $name || isset($plugins[$group][$name]);
		}

		return false;
	}
}
