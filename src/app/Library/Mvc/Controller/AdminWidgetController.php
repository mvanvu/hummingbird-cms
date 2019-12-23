<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Db\Adapter\Pdo\Mysql;
use MaiVu\Hummingbird\Lib\Mvc\Model\Config;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Helper\Widget as WidgetHelper;
use MaiVu\Hummingbird\Lib\Helper\Form as FormHelper;
use MaiVu\Hummingbird\Lib\Helper\Config as CmsConfig;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Helper\Asset;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\Editor;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Form\Form;
use MaiVu\Php\Filter;
use MaiVu\Php\Registry;

class AdminWidgetController extends AdminControllerBase
{
	/** @var Config */
	public $model = 'Config';

	/** @var string */
	public $pickedView = 'Widget';

	public function onConstruct()
	{
		parent::onConstruct();
		$template  = CmsConfig::getTemplate();
		$childPath = APP_PATH . '/Tmpl/Site/' . $template->name . '/Tmpl/Config.php';

		/** @var Registry $config */
		$config = $template->config;

		if (is_file($childPath))
		{
			$config->merge($childPath);
		}

		$positions       = $config->get('widgetPositions', []);
		$positionsSelect = '<select class="uk-select not-chosen uk-form-small">';

		foreach ($positions as $position)
		{
			$positionsSelect .= '<option value="' . $position . '">' . ucfirst($position) . '</option>';
		}

		$positionsSelect .= '</select>';
		Editor::initEditor();
		Asset::addFiles(
			[
				'icon.css',
				'icon.js',
				'media-modal.js',
				'ucm-item-modal.js',
				'widget.js',
			]
		);
		$this->view->setVars(
			[
				'positions'       => $positions,
				'positionsSelect' => $positionsSelect,
				'ajaxData'        => [
					'uri'      => Uri::getInstance(['uri' => 'widget/index'])->toString(),
					'token'    => FormHelper::getToken(),
					'language' => Language::getActiveCode(),
				],
			]
		);
	}

	protected function loadWidget($widgets)
	{
		$widgetName  = $this->request->getPost('name', ['string', 'trim'], '');
		$widgetClass = 'MaiVu\\Hummingbird\\Widget\\' . $widgetName . '\\' . $widgetName;

		if (isset($widgets[$widgetClass]))
		{
			$form = $this->view->getPartial('Widget/Item', ['widgetConfig' => $widgets[$widgetClass]]);
		}
		else
		{
			$this->dispatcher->forward(
				[
					'controller' => 'admin_error',
					'action'     => 'show',
				]
			);

			return false;
		}

		return $this->response->setJsonContent($form);
	}

	protected function saveWidget($widgets)
	{
		parse_str($this->request->getPost('serialize'), $serialize);
		$formData     = isset($serialize['FormData']) ? $serialize['FormData'] : [];
		$responseData = [
			'success' => false,
			'message' => [],
			'data'    => [],
		];

		if (empty($formData['name'])
			|| empty($formData['position'])
			|| empty($widgets['MaiVu\\Hummingbird\\Widget\\' . $formData['name'] . '\\' . $formData['name']])
		)
		{
			$responseData['message'] = 'Invalid data.';
		}
		else
		{
			/** @var Registry $widgetConfig */
			$widgetConfig = $widgets['MaiVu\\Hummingbird\\Widget\\' . $formData['name'] . '\\' . $formData['name']];
			$widgetData   = array_merge(
				$widgetConfig->toArray(),
				[
					'title'    => isset($formData['title']) ? Filter::clean($formData['title']) : '',
					'position' => Filter::clean($formData['position']),
					'params'   => [],
				]
			);

			$paramsForm = null;

			if ($widgetConfig->has('manifest.params'))
			{
				$paramsForm = new Form('FormData', $widgetConfig->get('manifest.params'));
				$paramsData = [];

				if (isset($formData['params']))
				{
					$paramsData = $paramsForm->bind($formData['params']);
				}

				if (!$paramsForm->isValid())
				{
					$responseData['message'] = implode('<br/>', $paramsForm->getMessages());

					return $this->response->setJsonContent($responseData);
				}

				$widgetData['params'] = $paramsData;
			}

			if (empty($formData['id']))
			{
				$config = new Config;
			}
			else
			{
				$config = Config::findFirst('id = ' . (int) $formData['id']) ?: (new Config);
			}

			$config->context = 'cms.config.widget.item.' . strtolower($formData['name']);
			$config->data    = $widgetData;

			if ($config->save())
			{
				$responseData['success'] = true;
				$responseData['data']    = $config->toArray();

				if (Language::isMultilingual())
				{
					$translationForm = new Form('FormData.params');
					/** @var Mysql $db */
					$db          = $this->getDI()->get('db');
					$prefixTable = $this->modelsManager->getModelPrefix();
					$db->execute('DELETE FROM ' . $prefixTable . 'translations WHERE translationId LIKE :translationId',
						[
							'translationId' => '%.config_data.id=' . $config->id . '.data',
						]
					);

					if (!empty($formData['translations']))
					{
						if ($paramsForm)
						{
							foreach ($paramsForm->getFields() as $field)
							{
								if ($field->get('translate'))
								{
									$translationForm->addField($field);
								}
							}
						}

						$hasParams = $translationForm->count();

						foreach ($formData['translations'] as $langCode => $langData)
						{
							$translationData = [];

							if (!empty($langData['title']))
							{
								$tranTitle = Filter::clean($langData['title'], 'string');
								$tranTitle = Filter::clean($tranTitle, 'trim');

								if (!empty($tranTitle) && $tranTitle !== $widgetData['title'])
								{
									$translationData['title'] = $tranTitle;
								}
							}

							if ($hasParams && isset($langData['params']))
							{
								$paramsData = $translationForm->bind($langData['params']);

								foreach ($paramsData as $name => $value)
								{
									if (!empty($value) && $value !== $widgetData['params'][$name])
									{
										$translationData['params'][$name] = $value;
									}
								}
							}

							$originValue     = json_encode($widgetData);
							$translatedValue = json_encode(array_merge($widgetData, $translationData));

							if ($originValue !== $translatedValue)
							{
								$insertSql = 'INSERT INTO ' . $prefixTable . 'translations(translationId,originalValue,translatedValue)'
									. ' VALUES (:translationId,:originalValue,:translatedValue)';
								$db->execute($insertSql,
									[
										'translationId'   => $langCode . '.config_data.id=' . $config->id . '.data',
										'originalValue'   => $originValue,
										'translatedValue' => $translatedValue,
									]
								);
							}
						}
					}
				}
			}
			else
			{
				$responseData['message'] = 'Can\'t save the widget.';
			}
		}

		return $this->response->setJsonContent($responseData);
	}

	protected function deleteWidget()
	{
		$id = (int) $this->request->getPost('id', ['int'], 0);

		if ($id > 0
			&& ($config = Config::findFirst('id = ' . $id))
		)
		{
			$config->delete();
		}

		return $this->response->setJsonContent('OK');
	}

	protected function orderWidget()
	{
		$widgets = $this->request->getPost('widgets', null, []);

		if (!empty($widgets))
		{
			foreach ($widgets as $position => $widgetIds)
			{
				$ordering = 0;

				foreach ($widgetIds as $widgetId)
				{
					$widgetId = (int) $widgetId;

					if ($widgetId > 0
						&& ($widget = Config::findFirst('id = ' . $widgetId))
					)
					{
						$widgetData             = json_decode($widget->data, true) ?: [];
						$widgetData['position'] = $position;
						$widget->assign(['ordering' => $ordering, 'data' => $widgetData])->save();
						$ordering++;
					}
				}
			}
		}

		return $this->response->setJsonContent('OK');
	}

	public function indexAction()
	{
		$this->tag->setTitle(Text::_('sys-widgets'));
		$widgets = WidgetHelper::getWidgets();

		if ($this->request->isPost()
			&& $this->request->isAjax()
			&& User::getInstance()->access('super')
			&& FormHelper::checkToken()
		)
		{
			$requestType = $this->request->getPost('requestType', ['string', 'trim'], '');

			switch ($requestType)
			{
				case 'loadWidget':

					return $this->loadWidget($widgets);

				case 'saveWidget':

					return $this->saveWidget($widgets);

				case 'deleteWidget':

					return $this->deleteWidget();

				case 'orderWidget':

					return $this->orderWidget();
			}

		}

		$this->view->setVars(
			[
				'widgets'     => $widgets,
				'widgetItems' => WidgetHelper::getWidgetItems(),
			]
		);
	}
}
