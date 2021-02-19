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
	protected static $events = [];

	/**
	 * @var array
	 */
	protected static $plugins = null;

	public static function trigger($eventName, $arguments = [], $groups = null)
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
				foreach ($plugins[$group] as $class => $plugin)
				{
					$handler = static::getHandler($plugin);

					if ($handler instanceof Plugin)
					{
						if ($handler->isDetached())
						{
							unset(static::$plugins[$group][$class]);
						}
						elseif (is_callable([$handler, $eventName]))
						{
							$results[] = call_user_func_array([$handler, $eventName], $arguments);
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
			}
		}

		return static::$plugins;
	}

	public static function loadPluginLanguage($group, $plugin)
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
		static $handlers = [];
		$class = Constant::getNamespacePlugin($plugin->group, $plugin->name);

		if (!array_key_exists($class, $handlers))
		{
			if (class_exists($class))
			{
				$handlers[$class] = new $class(static::createConfig($plugin));
			}
			else
			{
				$handlers[$class] = null;
			}
		}

		return $handlers[$class];
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

	public static function getPlugin(string $group, string $name): ?PluginModel
	{
		return static::getPlugins()[$group][$name] ?? null;
	}

	public static function getGroup($group)
	{
		return static::getPlugins()[$group] ?? [];
	}

	public static function exists($group, $plugin = null): bool
	{
		$plugins = static::getPlugins();

		if (isset($plugins[$group]))
		{
			return null === $plugin || (is_string($plugin) && isset($plugins[$group][$plugin]));
		}

		return false;
	}
}
