<?php

namespace App\Mvc\Controller;

use App\Form\Field\CmsTemplate;
use App\Helper\Assets;
use App\Helper\Template;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\Widget as WidgetHelper;
use App\Mvc\Model\Config;
use App\Traits\Permission;
use MaiVu\Php\Filter;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Form\FormsManager;
use MaiVu\Php\Registry;

class AdminWidgetController extends AdminControllerBase
{
	/**
	 * @var Config
	 */
	public $model = 'Config';

	/**
	 * @var string
	 */

	public $mainEditFormName = 'Widget';

	/**
	 * @var string
	 */
	public $pickedView = 'Widget';

	/**
	 * @var string
	 */
	public $role = 'super';

	use Permission;

	public function onConstruct()
	{
		parent::onConstruct();
		$templateId      = $this->persistent->get('widget.templateId', Template::getTemplate()->id);
		$positions       = preg_split('/\r\n|\n/', Template::getTemplate($templateId)->getParams()->get('positions'), -1, PREG_SPLIT_NO_EMPTY);
		$positions       = Filter::clean($positions, ['unique', 'trim:array']);
		$positionsSelect = '<select class="uk-select not-chosen uk-form-small">';

		foreach ($positions as $position)
		{
			$positionsSelect .= '<option value="' . $position . '">' . ucfirst($position) . '</option>';
		}

		$positionsSelect .= '</select>';
		Assets::add('js/widget.js');
		$this->view->setVars(
			[
				'positions'       => $positions,
				'positionsSelect' => $positionsSelect,
				'templates'       => CmsTemplate::create(
					[
						'name'         => 'widgetTemplateId',
						'class'        => 'uk-select',
						'value'        => $templateId,
						'defaultFirst' => true,
					]
				),
			]
		);
	}

	public function indexAction()
	{
		Text::script('confirm-delete-widget');
		Text::script('widget-removed-msg');
		Text::script('ordering-updated-msg');
		$this->tag->setTitle(Text::_('sys-widgets'));
		$this->view->setVars(
			[
				'widgets'     => WidgetHelper::getWidgets(),
				'widgetItems' => WidgetHelper::getWidgetItems(),
			]
		);
	}

	public function toggleTemplateAction()
	{
		$templateId = $this->request->getPost('widgetTemplateId', 'absint', 0);

		if (isset(Template::getTemplates()[$templateId]))
		{
			$this->persistent->set('widget.templateId', $templateId);
		}

		return $this->uri::redirect($this->uri->routeTo('index'));
	}

	public function handleWidgetAction($position, $name, $id)
	{
		if ($this->request->isMethod('DELETE'))
		{
			return $this->response->setJsonContent($this->model->delete());
		}

		if ($config = WidgetHelper::getConfig($name))
		{
			$willRefresh  = false;
			$formsManager = new FormsManager(
				[
					'general' => Form::create('Widget.data',
						[
							[
								'name'      => 'title',
								'type'      => 'Text',
								'label'     => 'title',
								'translate' => true,
								'value'     => null,
								'class'     => 'uk-input',
								'filters'   => ['string', 'trim'],
							],
							[
								'name'    => 'menuPattern',
								'type'    => 'Switcher',
								'label'   => 'uri-assigns',
								'value'   => 'Y',
								'filters' => ['yesNo'],
							],
							[
								'name'        => 'pattern',
								'type'        => 'Text',
								'label'       => 'pattern',
								'description' => 'pattern-desc',
								'class'       => 'uk-input',
								'showOn'      => 'menuPattern:Y',
							],
						]
					),
					'params'  => Form::create('Widget.data.params', $config->get('manifest.params', [])),
				]
			);

			if ($this->request->isMethod('POST'))
			{
				// Save widget
				if ($formsManager->isValidRequest())
				{
					$isNew                 = empty($id);
					$validData             = $formsManager->getData()->get('Widget.data', []);
					$validData['position'] = $position;
					$storeData             = [
						'context' => 'cms.config.widget.item.' . $config->get('manifest.name'),
						'data'    => $validData,
					];

					if ($isNew)
					{
						$item = $this->model::findFirst(
							[
								'conditions' => 'context LIKE \'cms.config.widget.item.%\'',
								'order'      => 'ordering DESC',
							]
						);

						$storeData['ordering'] = $item ? (int) $item->ordering + 1 : 0;
					}

					if ($this->model->assign($storeData)->save())
					{
						$willRefresh = $isNew;
						$id          = $this->model->id;
						$this->saveTranslations($formsManager);
						$this->flashSession->success(Text::_('widget-saved-msg'));
					}
				}
			}
			elseif ($this->model->id)
			{
				$formsManager->bind(
					[
						'Widget' => [
							'data' => Registry::parseData($this->model->data),
							'i18n' => $this->model->getI18nData(),
						],
					]
				);
			}

			$this->view->setMainView('Raw');
			$this->view->pick('Widget/Item');
			$this->view->setVars(
				[
					'action'       => Uri::route('widget/' . $position . '/' . $name . '/' . $id),
					'widgetConfig' => $config,
					'formsManager' => $formsManager,
					'willRefresh'  => $willRefresh,
				]
			);
		}
		else
		{
			$this->dispatcher->forward(
				[
					'controller' => 'admin_error',
					'action'     => 'show',
				]
			);
		}
	}

	public function orderAction()
	{
		if ($this->request->isAjax()
			&& $this->request->isPost()
			&& $widgets = $this->request->getPost('widgets', null, [])
		)
		{
			foreach (Filter::clean($widgets, ['unique']) as $position => $widgetIds)
			{
				$ordering = 0;

				foreach ($widgetIds as $widgetId)
				{
					if ($widget = Config::findFirst('id = ' . (int) $widgetId))
					{
						$widgetData             = json_decode($widget->data, true) ?: [];
						$widgetData['position'] = $position;
						$widget->assign(['ordering' => $ordering, 'data' => $widgetData])->save();
						$ordering++;
					}
				}
			}

			return $this->response->setJsonContent('OK');
		}
	}
}
