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

	protected static $extraPaths = [];

	public static function setExtraPath(string $path): array
	{
		$path = FileSystem::cleanPath($path);

		if (!in_array($path, static::$extraPaths) && is_dir($path))
		{
			static::$extraPaths[] = $path;
		}

		return static::$extraPaths;
	}

	public static function renderPosition($position, $wrapper = null)
	{
		$results = [];

		if ($widgetItems = (static::getWidgetItems()[$position] ?? null))
		{
			foreach ($widgetItems as $widget)
			{
				$results[] = static::render($widget, $wrapper);
			}
		}

		return implode(PHP_EOL, $results);
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

			$isSite       = Uri::isClient('site');
			$multilingual = Language::isMultilingual() && $isSite;

			foreach ($entities as $widget)
			{
				$parts = explode('.', $widget->context);
				$name  = $parts[count($parts) - 1];

				if ($widgetConfig = static::getConfig($name))
				{
					$data = Registry::create($widget->data);

					if ($multilingual && $translations = $widget->getTranslations())
					{
						$data->merge($translations);
					}

					if ($isSite
						&& 'Y' === $data->get('menuPattern')
						&& ($pattern = $data->get('pattern'))
						&& !Uri::is($pattern)
					)
					{
						continue;
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
			$widgetPaths     = [];
			$tmplPath        = Template::getTemplatePath() . '/Widget';

			if (is_dir($tmplPath))
			{
				$widgetPaths[] = $tmplPath;
			}

			$widgetPaths[] = WIDGET_PATH;

			foreach (array_merge($widgetPaths, static::$extraPaths) as $widgetPath)
			{
				foreach (FileSystem::scanDirs($widgetPath) as $widgetDir)
				{
					(new Loader)->registerNamespaces([Constant::NAMESPACE_WIDGET => $widgetDir], true)->register();
					static::appendWidget($widgetDir);
				}
			}
		}

		return static::$widgets;
	}

	protected static function appendWidget($widgetPath)
	{
		$widgetName  = basename($widgetPath);
		$widgetClass = Constant::getNamespaceWidget($widgetName);
		$configFile  = $widgetPath . '/config.php';

		if (class_exists($widgetClass) && is_file($configFile))
		{
			static::$widgets[$widgetClass] = Registry::create()->set('manifest', Registry::parseData($configFile));
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
