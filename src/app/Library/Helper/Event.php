<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use MaiVu\Hummingbird\Lib\Mvc\Model\Config as ConfigModel;
use MaiVu\Php\Registry;

class Event
{
	/** @var array */
	protected static $events = [];

	/** @var array */
	protected static $plugins = null;

	/** @var array */
	protected static $allPlugins = null;

	public static function getPlugins($all = false)
	{
		if (null === self::$plugins)
		{
			$entities = ConfigModel::find(
				[
					'conditions' => 'context LIKE :context:',
					'bind'       => [
						'context' => 'cms.config.plugin.%',
					],
				]
			);

			foreach ($entities as $entity)
			{
				$pluginConfig = new Registry($entity->data);
				$group        = $pluginConfig->get('manifest.group');
				$name         = $pluginConfig->get('manifest.name');
				$pluginClass  = 'MaiVu\\Hummingbird\\Plugin\\' . $group . '\\' . $name . '\\' . $name;

				if (!class_exists($pluginClass))
				{
					$entity->delete();
					continue;
				}

				if ($pluginConfig->get('active', false))
				{
					self::$plugins[$group][$pluginClass] = $pluginConfig;
					self::loadPluginLanguage($group, $name);
				}

				self::$allPlugins[$group][$pluginClass] = $pluginConfig;
			}
		}

		return $all ? self::$allPlugins : self::$plugins;
	}

	public static function getHandler($class, Registry $config)
	{
		static $handlers = [];

		if (!isset($handlers[$class]))
		{
			if (class_exists($class))
			{
				$handlers[$class] = new $class($config);
			}
			else
			{
				$handlers[$class] = false;
			}
		}

		return $handlers[$class];
	}

	public static function trigger($eventName, $arguments = [], $groups = null)
	{
		$results = [];
		$plugins = self::getPlugins();

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
				 * @var  string   $class
				 * @var  Registry $config
				 */
				foreach ($plugins[$group] as $class => $config)
				{
					$handler = self::getHandler($class, $config);

					if (false !== $handler && is_callable([$handler, $eventName]))
					{
						$results[] = call_user_func_array([$handler, $eventName], $arguments);
					}
				}
			}
		}

		return $results;
	}

	public static function exists($group, $plugin = null)
	{
		$plugins = self::getPlugins();

		if (isset($plugins[$group]))
		{
			return null === $plugin || (is_string($plugin) && isset($plugins[$group][$plugin]));
		}

		return false;
	}

	public static function loadPluginLanguage($group, $plugin)
	{
		$langCode = Language::getActiveCode();
		$langFile = PLUGIN_PATH . '/' . $group . '/' . $plugin . '/Language/' . $langCode . '.php';

		if (is_file($langFile)
			&& ($content = include $langFile)
		)
		{
			Language::load($content, $langCode);
		}
	}
}
