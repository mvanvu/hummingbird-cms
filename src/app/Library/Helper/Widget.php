<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use Phalcon\Loader;
use MaiVu\Hummingbird\Lib\Mvc\Model\Translation;
use MaiVu\Hummingbird\Lib\Widget as CmsWidget;
use MaiVu\Hummingbird\Lib\Mvc\Model\Config as ConfigModel;
use MaiVu\Hummingbird\Lib\Form\Form;
use MaiVu\Hummingbird\Lib\Form\Field;
use MaiVu\Php\Registry;

class Widget
{
	/** @var array */
	protected static $widgets = null;

	protected static function appendWidget($widgetPath, $coreWidgets)
	{
		$widgetName  = basename($widgetPath);
		$widgetClass = 'MaiVu\\Hummingbird\\Widget\\' . $widgetName . '\\' . $widgetName;
		$configFile  = $widgetPath . '/Config.php';

		if (class_exists($widgetClass)
			&& is_file($configFile)
		)
		{
			$widgetConfig = new Registry;
			$widgetConfig->set('isCmsCore', in_array($widgetClass, $coreWidgets));
			$widgetConfig->set('manifest', $widgetConfig->parse($configFile));
			self::$widgets[$widgetClass] = $widgetConfig;
		}
	}

	public static function getWidgets()
	{
		if (null === self::$widgets)
		{
			self::$widgets   = [];
			$coreWidgets     = Config::get('core.widgets', []);
			$template        = Config::getTemplate()->name;
			$widgetTmplPaths = [
				APP_PATH . '/Tmpl/Site/' . $template . '/Tmpl/Widget',
				APP_PATH . '/Tmpl/Site/' . $template . '/Widget',
			];

			foreach ($widgetTmplPaths as $widgetTmplPath)
			{
				if (is_dir($widgetTmplPath))
				{
					(new Loader)
						->registerNamespaces(
							[
								'MaiVu\\Hummingbird\\Widget' => $widgetTmplPath,
							]
						)
						->register();

					foreach (FileSystem::scanDirs($widgetTmplPath) as $widgetPath)
					{
						self::appendWidget($widgetPath, $coreWidgets);
					}
				}
			}

			foreach (FileSystem::scanDirs(WIDGET_PATH) as $widgetPath)
			{
				self::appendWidget($widgetPath, $coreWidgets);
			}

			$plugins = Event::getPlugins();

			if (!empty($plugins['Cms']))
			{
				/** @var Registry $pluginConfig */
				foreach ($plugins['Cms'] as $pluginConfig)
				{
					$widgetPluginPath = PLUGIN_PATH . '/Cms/' . $pluginConfig->get('manifest.name') . '/Widget';

					if (is_dir($widgetPluginPath))
					{
						foreach (FileSystem::scanDirs($widgetPluginPath) as $widgetPath)
						{
							self::appendWidget($widgetPath, $coreWidgets);
						}
					}
				}
			}
		}

		return self::$widgets;
	}

	public static function renderPosition($position, $wrapper = null)
	{
		$results     = '';
		$widgetItems = self::getWidgetItems();

		if (isset($widgetItems[$position]))
		{
			foreach ($widgetItems[$position] as $widget)
			{
				$results .= self::render($widget, $wrapper);
			}
		}

		return $results;
	}

	public static function createWidget($widgetName, $params = [], $render = true, $wrapper = null)
	{
		$widgets     = self::getWidgets();
		$widgetClass = 'MaiVu\\Hummingbird\\Widget\\' . $widgetName . '\\' . $widgetName;

		if (!isset($widgets[$widgetClass]))
		{
			return false;
		}

		$widgetConfig = clone $widgets[$widgetClass];
		$widgetConfig->set('params', $widgetConfig->parse($params));
		$widgetConfig->set('id', null);

		if ($render)
		{
			return self::render($widgetConfig, $wrapper);
		}

		return $widgetConfig;
	}

	public static function render(Registry $widgetConfig, $wrapper = null)
	{
		$widgets     = self::getWidgets();
		$name        = $widgetConfig->get('manifest.name');
		$widgetClass = 'MaiVu\\Hummingbird\\Widget\\' . $name . '\\' . $name;

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

	public static function getWidgetItems()
	{
		static $widgetItems = null;

		if (null === $widgetItems)
		{
			$widgetItems = [];
			$widgets     = self::getWidgets();
			$entities    = ConfigModel::find(
				[
					'conditions' => 'context LIKE :context:',
					'bind'       => [
						'context' => 'cms.config.widget.item.%',
					],
					'order'      => 'ordering ASC',
				]
			);

			$translate = Language::isMultilingual() && Uri::isClient('site');

			foreach ($entities as $widget)
			{
				/** @var ConfigModel $widget */
				$widgetConfig = new Registry($widget->data);
				$widgetConfig->set('id', $widget->id);
				$name        = $widgetConfig->get('manifest.name');
				$widgetClass = 'MaiVu\\Hummingbird\\Widget\\' . $name . '\\' . $name;

				if (isset($widgets[$widgetClass]))
				{
					if ($translate)
					{
						$translations = $widget->getTranslations();

						if (isset($translations['data']))
						{
							$widgetConfig->merge($translations['data']);
						}
					}

					// Merge manifest and some global data
					$widgetConfig->merge($widgets[$widgetClass]);
					$widgetItems[$widgetConfig->get('position')][] = $widgetConfig;
				}
			}
		}

		return $widgetItems;
	}

	public static function renderForm(Registry $widgetData)
	{
		$widgetId   = $widgetData->get('id', 0, 'uint');
		$idIndex    = $widgetId ?: uniqid();
		$configForm = '<div class="widget-params">';
		$form       = new Form('FormData',
			[
				[
					'name'    => 'id',
					'type'    => 'Hidden',
					'value'   => $widgetId,
					'id'      => 'FormData-id' . $idIndex,
					'filters' => ['uint'],
				],
				[
					'name'     => 'name',
					'type'     => 'Hidden',
					'required' => true,
					'value'    => $widgetData->get('manifest.name'),
					'id'       => 'FormData-name' . $idIndex,
					'filters'  => ['string', 'trim'],
				],
				[
					'name'      => 'title',
					'type'      => 'Text',
					'label'     => 'title',
					'translate' => true,
					'value'     => $widgetData->get('title'),
					'id'        => 'FormData-title' . $idIndex,
					'filters'   => ['string', 'trim'],
				],
				[
					'name'     => 'position',
					'type'     => 'Hidden',
					'required' => true,
					'value'    => $widgetData->get('position'),
					'id'       => 'FormData-position' . $idIndex,
					'filters'  => ['string', 'trim'],
				],
			]
		);

		$transParamsData = [];

		if ($widgetId && Language::isMultilingual())
		{
			$transData = Translation::find(
				[
					'conditions' => 'translationId LIKE :translationId:',
					'bind'       => [
						'translationId' => '%.config_data.id=' . $widgetId . '.data',
					],
				]
			);

			if ($transData->count())
			{
				$titleField = $form->getField('title');

				foreach ($transData as $transDatum)
				{
					$registry = new Registry($transDatum->translatedValue);
					$parts    = explode('.', $transDatum->translationId);
					$language = $parts[0];
					$titleField->setTranslationData($registry->get('title'), $language);

					foreach ($registry->get('params', []) as $name => $value)
					{
						$transParamsData[$name][$language] = $value;
					}
				}
			}
		}

		$configForm .= $form->renderFields();

		if ($widgetData->has('manifest.params'))
		{
			$form = new Form('FormData.params', $widgetData->get('manifest.params', []));
			$form->bind($widgetData->get('params', []), $transParamsData);

			foreach ($form->getFields() as $field)
			{
				/** @var Field $field */
				$field->setId($field->getId() . $idIndex);
				$configForm .= $field->render();
			}
		}

		$configForm .= '</div>';

		return $configForm;
	}
}
