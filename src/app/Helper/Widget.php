<?php

namespace App\Helper;

use App\Mvc\Model\Config as ConfigModel;
use App\Widget\Widget as CmsWidget;
use MaiVu\Php\Registry;
use Phalcon\Loader;

class Widget
{
	/** @var array */
	protected static $widgets = null;

	public static function renderPosition($position, $wrapper = null)
	{
		$results     = '';
		$widgetItems = static::getWidgetItems();

		if (isset($widgetItems[$position]))
		{
			foreach ($widgetItems[$position] as $widget)
			{
				$results .= static::render($widget, $wrapper) . PHP_EOL;
			}
		}

		return rtrim($results, PHP_EOL);
	}

	public static function getWidgetItems()
	{
		static $widgetItems = null;

		if (null === $widgetItems)
		{
			$widgetItems = [];
			$entities    = ConfigModel::find(
				[
					'conditions' => 'context LIKE :context:',
					'bind'       => [
						'context' => 'cms.config.widget.item.%',
					],
					'order'      => 'ordering ASC',
				]
			);

			$multilingual = Language::isMultilingual() && Uri::isClient('site');

			foreach ($entities as $widget)
			{
				$parts = explode('.', $widget->context);
				$name  = $parts[count($parts) - 1];

				if ($widgetConfig = static::getConfig($name))
				{
					$data = new Registry($widget->data);

					if ($multilingual && $translations = $widget->getTranslations())
					{
						$data->merge($translations);
					}

					$widgetConfig->set('id', $widget->id);
					$widgetConfig->merge($data);
					$widgetItems[$widgetConfig->get('position')][] = $widgetConfig;
				}
			}
		}

		return $widgetItems;
	}

	public static function getConfig($widgetName)
	{
		$className = Constant::getNamespaceWidget(ucfirst($widgetName));

		return static::getWidgets()[$className] ?? false;
	}

	public static function getWidgets()
	{
		if (null === static::$widgets)
		{
			static::$widgets = [];
			$widgetPaths     = [
				Template::getTemplatePath() . '/Widget',
				WIDGET_PATH,
			];

			if ($group = Event::getGroup('Cms'))
			{
				foreach ($group as $plugin)
				{
					$widgetPaths[] = PLUGIN_PATH . '/Cms/' . $plugin->name . '/Widget';
				}
			}

			foreach ($widgetPaths as $widgetPath)
			{
				if (is_dir($widgetPath))
				{
					foreach (FileSystem::scanDirs($widgetPath) as $widgetDir)
					{
						(new Loader)->registerNamespaces([Constant::NAMESPACE_WIDGET => $widgetDir], true)->register();
						static::appendWidget($widgetDir);
					}
				}
			}
		}

		return static::$widgets;
	}

	protected static function appendWidget($widgetPath)
	{
		$widgetName  = basename($widgetPath);
		$widgetClass = Constant::getNamespaceWidget($widgetName);
		$configFile  = $widgetPath . '/Config.php';

		if (class_exists($widgetClass) && is_file($configFile))
		{
			$widgetConfig = new Registry;
			$widgetConfig->set('isCmsCore', in_array($widgetClass, Config::get('core.widgets', [])));
			$widgetConfig->set('manifest', $widgetConfig->parse($configFile));
			static::$widgets[$widgetClass] = $widgetConfig;
		}
	}

	public static function render(Registry $widgetConfig, $wrapper = null)
	{
		$widgets     = static::getWidgets();
		$name        = $widgetConfig->get('manifest.name');
		$widgetClass = Constant::getNamespaceWidget($name);

		if (isset($widgets[$widgetClass]))
		{
			$widget = new $widgetClass($widgetConfig);

			if ($widget instanceof CmsWidget)
			{
				return $widget->render($wrapper);
			}
		}

		return null;
	}

	public static function createWidget(string $widgetName, array $params = [], bool $render = true, string $wrapper = null)
	{
		$widgets     = static::getWidgets();
		$widgetClass = Constant::getNamespaceWidget($widgetName);

		if (!isset($widgets[$widgetClass]))
		{
			return false;
		}

		$widgetConfig = clone $widgets[$widgetClass];
		$widgetConfig->set('id', 0);

		foreach ($params as $name => $value)
		{
			$widgetConfig->set('params.' . $name, $value);
		}

		if ($render)
		{
			return static::render($widgetConfig, $wrapper);
		}

		return $widgetConfig;
	}
}
