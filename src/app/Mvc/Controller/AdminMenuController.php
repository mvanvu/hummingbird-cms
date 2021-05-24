<?php

namespace App\Mvc\Controller;

use App\Helper\Assets;
use App\Helper\Menu;
use App\Helper\Text;
use App\Helper\User;
use App\Mvc\Model\Config as ConfigModel;
use App\Traits\Permission;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Form\FormsManager;
use MaiVu\Php\Registry;

class AdminMenuController extends AdminControllerBase
{
	/**
	 * @var ConfigModel
	 */
	public $model = 'Config';

	/**
	 * @var string
	 */
	public $pickedView = 'Menu';

	/**
	 * @var string
	 */
	public $mainEditFormName = 'Menu';

	/**
	 * @var null
	 */
	public $stateField = null;

	/**
	 * @var string
	 */
	public $role = 'super';

	use Permission;

	public function indexAction()
	{
		$menuType  = $this->persistent->get('admin.menu.type', null);
		$menuTypes = Menu::getMenuTypes()->toArray();
		$found     = false;

		if ($menuType)
		{
			foreach ($menuTypes as $mType)
			{
				if ($menuType === $mType['data'])
				{
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				$menuType = $menuTypes[0]['data'];
				$this->persistent->set('admin.menu.type', $menuType);
			}
		}
		elseif (isset($menuTypes[0]['data']))
		{
			$menuType = $menuTypes[0]['data'];
			$this->persistent->set('admin.menu.type', $menuType);
		}

		Text::script('toggle-menu-type-confirm');
		Text::script('menu-type-name');
		Text::script('empty-menu-name-msg');
		Text::script('remove-menu-item-confirm');
		Text::script('remove-menu-type-confirm');
		Assets::jQueryCore();
		Assets::add(
			[
				'css/jquery.nestable.css',
				'js/jquery.nestable.js',
				'js/menu.js',
			]
		);

		$this->view->setVars(
			[
				'registeredMenus' => Menu::getRegisteredMenus(),
				'menuTypes'       => $menuTypes,
				'menuType'        => $menuType,
			]
		);

		parent::indexAction();
	}

	public function saveMenuAction()
	{
		if ($this->isValidRequest())
		{
			$formsManager = $this->getMenuFormsManager();

			if (!$formsManager->isValidRequest())
			{
				Form::clearSessionMessages();

				return $this->response->setJsonContent(
					[
						'success' => false,
						'message' => implode('<br/>', $formsManager->getMessages()),
					]
				);
			}

			$menuData = $formsManager->getData()->get('Menu.data', []);

			if ($menuData['id'] > 0
				&& ($menuItemEntity = ConfigModel::findFirst($menuData['id']))
				&& $menuItemEntity->context === 'cms.menu.item'
			)
			{
				$isNew                = false;
				$entityData           = json_decode($menuItemEntity->data, true) ?: [];
				$menuData['parentId'] = (int) ($entityData['parentId'] ?? 0);
			}
			else
			{
				$isNew                    = true;
				$menuItemEntity           = new ConfigModel;
				$menuItemEntity->context  = 'cms.menu.item';
				$menuItemEntity->ordering = 0;
				$menuData['parentId']     = 0;
			}

			$menuItemEntity->data = json_encode($menuData);

			if ($menuItemEntity->save())
			{
				$this->model = $menuItemEntity;
				$this->saveTranslations($formsManager);

				return $this->response->setJsonContent(
					[
						'success' => true,
						'message' => Text::_('menu-item-' . ($isNew ? 'saved' : 'updated'), ['title' => $menuData['title']]),
						'data'    => $this->view->getPartial('Menu/AdminList', ['menuType' => $menuData['menu']]),
					]
				);
			}

			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => 'Err. Cannot save the menu.',
				]
			);
		}

		$this->dispatcher->forward(
			[
				'controller' => 'admin_error',
				'action'     => 'show',
			]
		);
	}

	protected function isValidRequest()
	{
		if ($this->request->isAjax()
			&& $this->request->isPost()
			&& User::getActive()->is('super')
		)
		{
			return true;
		}

		return false;
	}

	protected function getMenuFormsManager($type = null)
	{
		$itemsOptions = [];
		$menus        = Menu::getRegisteredMenus();

		if (null === $type)
		{
			$type = $this->request->get('Menu', [])['data']['type'] ?? '';
		}

		foreach ($menus as $mType => $item)
		{
			$itemsOptions[$mType] = $mType;
		}

		$formsManager = new FormsManager;
		$formsManager->set(
			'Menu',
			Form::create('Menu.data',
				[
					[
						'name'    => 'id',
						'type'    => 'Hidden',
						'filters' => ['uint'],
					],
					[
						'name'      => 'title',
						'type'      => 'Text',
						'label'     => 'title',
						'translate' => true,
						'class'     => 'uk-input',
						'filters'   => ['string', 'trim'],
					],
					[
						'name'    => 'icon',
						'label'   => 'icon-label',
						'type'    => 'CmsIcon',
						'filters' => ['html', 'trim'],
					],
					[
						'name'     => 'menu',
						'type'     => 'CmsMenuType',
						'required' => true,
						'class'    => 'uk-select',
						'rules'    => ['options'],
					],
					[
						'name'     => 'type',
						'type'     => 'Select',
						'required' => true,
						'options'  => $itemsOptions,
						'class'    => 'uk-select',
						'rules'    => ['options'],
					],
					[
						'name'    => 'target',
						'type'    => 'Select',
						'label'   => 'menu-target',
						'class'   => 'uk-select',
						'options' => [
							''       => 'target-self',
							'_blank' => 'target-blank',
						],
						'rules'   => ['options'],
					],
					[
						'name'    => 'nofollow',
						'type'    => 'Select',
						'label'   => 'menu-nofollow',
						'class'   => 'uk-select',
						'value'   => 'N',
						'options' => [
							'Y' => 'yes',
							'N' => 'no',
						],
						'rules'   => ['options'],
					],
					[
						'name'    => 'templateId',
						'type'    => 'CmsTemplate',
						'label'   => 'assign-for-template',
						'class'   => 'uk-select',
						'options' => [
							'0' => 'default-template',
						],
						'filters' => ['uint'],
						'rules'   => ['Options'],
					],
				]
			)
		);

		$formsManager->set('params', Form::create('Menu.data.params', $menus[$type]['params'] ?? []));

		return $formsManager;
	}

	public function removeMenuItemAction()
	{
		if ($this->isValidRequest())
		{
			$menuId = (int) $this->request->getPost('menuId', ['int'], 0);

			if ($menuId > 0
				&& ($menuItem = ConfigModel::findFirst('id = ' . $menuId))
				&& $menuItem->context == 'cms.menu.item'
				&& $menuItem->delete()
			)
			{
				return $this->response->setJsonContent(
					[
						'success' => true,
						'message' => Text::_('menu-item-removed-success-msg'),
					]
				);
			}

			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => Text::_('menu-item-removed-fail-msg'),
				]
			);
		}
	}

	public function createMenuTypeAction()
	{
		if ($this->isValidRequest())
		{
			$menuTypeName = $this->request->getPost('menuType', ['alphanum'], '');

			if (empty($menuTypeName))
			{
				return $this->response->setJsonContent(
					[
						'success' => false,
						'message' => Text::_('empty-menu-name-msg'),
					]
				);
			}

			$params = [
				'conditions' => 'context = :context: AND data = :menuType:',
				'bind'       => [
					'context'  => 'cms.menu.type',
					'menuType' => $menuTypeName,
				],
			];

			if (ConfigModel::findFirst($params))
			{
				return $this->response->setJsonContent(
					[
						'success' => false,
						'message' => Text::_('menu-type-exists-msg', ['menuType' => $menuTypeName]),
					]
				);
			}

			$menu           = new ConfigModel;
			$menu->context  = 'cms.menu.type';
			$menu->data     = $menuTypeName;
			$menu->ordering = 0;

			if ($menu->save())
			{
				$options = '';

				foreach (ConfigModel::find('context = "cms.menu.type"') as $menuType)
				{
					$options .= '<option value="' . $menuType->data . '">' . $menuType->data . '</option>';
				}

				return $this->response->setJsonContent(
					[
						'success' => true,
						'message' => null,
						'data'    => $options,
					]
				);
			}

			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => 'Can\' save, please contact the developer',
				]
			);
		}
	}

	public function toggleMenuTypeAction()
	{
		if ($this->isValidRequest())
		{
			$menuTypeName = $this->request->getPost('menuType', ['alphanum'], '');
			$params       = [
				'conditions' => 'context = :context: AND data = :menuType:',
				'bind'       => [
					'context'  => 'cms.menu.type',
					'menuType' => $menuTypeName,
				],
			];

			if (!($menuTypeEntity = ConfigModel::findFirst($params)))
			{
				return $this->response->setJsonContent(
					[
						'success' => false,
						'message' => Text::_('menu-type-not-exists-msg', ['menuType' => $menuTypeName]),
					]
				);
			}

			$this->persistent->set('admin.menu.type', $menuTypeName);

			return $this->response->setJsonContent(
				[
					'success' => true,
					'message' => null,
					'data'    => $this->view->getPartial('Menu/AdminList', ['menuType' => $menuTypeName]),
				]
			);
		}
	}

	public function nestableItemsAction()
	{
		if ($this->isValidRequest())
		{
			$items = $this->request->getPost('items', null, []);
			$this->handleNestableItems($items);

			return $this->response->setJsonContent('OK');
		}
	}

	protected function handleNestableItems($items, $parentId = 0)
	{
		$ordering = 0;

		foreach ($items as $item)
		{
			$id = isset($item['id']) ? (int) $item['id'] : 0;

			if ($id)
			{
				$params = [
					'conditions' => 'id = :id: AND context = :context:',
					'bind'       => [
						'id'      => $id,
						'context' => 'cms.menu.item',
					],
				];

				if ($itemEntity = ConfigModel::findFirst($params))
				{
					$itemData             = json_decode($itemEntity->data, true) ?: [];
					$itemData['parentId'] = $parentId;
					$itemEntity->ordering = $ordering++;
					$itemEntity->data     = json_encode($itemData);

					if ($itemEntity->save() && !empty($item['children']))
					{
						$this->handleNestableItems($item['children'], $id);
					}
				}
			}
		}
	}

	public function itemAction()
	{
		$type  = $this->request->get('type', ['trim', 'string'], '');
		$id    = (int) $this->request->get('id', ['absint'], 0);
		$menus = Menu::getRegisteredMenus();

		if (isset($menus[$type]))
		{
			$formsManager = $this->getMenuFormsManager($type);
			$menuForm     = $formsManager->get('Menu');
			$menuForm->getField('id')->setValue($id);
			$menuForm->getField('type')->setValue($type);
			$menuForm->getField('menu')->setValue($this->persistent->get('admin.menu.type'));

			if ($id > 0 && ($item = ConfigModel::findFirst($id)))
			{
				$formsManager->bind(
					[
						'Menu' => [
							'data' => Registry::parseData($item->data),
							'i18n' => $item->getI18nData(),
						],
					]
				);
			}

			$this->view->setMainView('Raw');
			$this->view->setVars(
				[
					'id'           => $id,
					'type'         => $type,
					'formsManager' => $formsManager,
				]
			);
		}
		else
		{
			$this->dispatcher->forward(
				[
					'controller' => 'error',
					'action'     => 'show',
				]
			);
		}
	}

	public function renameMenuTypeAction()
	{
		if ($this->isValidRequest())
		{
			$menuType = $this->request->getPost('menuType', ['alphanum'], '');
			$newName  = $this->request->getPost('newName', ['alphanum'], '');
			$params   = [
				'conditions' => 'context = :context: AND data = :menuType:',
				'bind'       => [
					'context'  => 'cms.menu.type',
					'menuType' => $newName,
				],
			];

			if ($menuType !== $newName
				&& !empty($newName)
				&& !ConfigModel::findFirst($params)
			)
			{
				$params['bind']['menuType'] = $menuType;

				if (($entity = ConfigModel::findFirst($params))
					&& $entity->assign(['data' => $newName])->save()
				)
				{
					if ($items = Menu::getMenuItems($menuType))
					{
						foreach ($items as $item)
						{
							if ($entity = ConfigModel::findFirst('id = ' . (int) $item->id))
							{
								$data         = json_decode($item->rawData, true) ?: [];
								$data['menu'] = $newName;
								$entity->assign(['data' => $data])->save();
							}
						}
					}

					if ($menuType === $this->persistent->get('admin.menu.type', null))
					{
						$this->persistent->set('admin.menu.type', $newName);
					}

					return $this->response->setJsonContent(
						[
							'success' => true,
							'message' => null,
							'data'    => [
								'menuType' => $menuType,
								'newName'  => $newName,
							],
						]
					);
				}
			}
		}

		return $this->response->setJsonContent(
			[
				'success' => false,
				'message' => null,
			]
		);
	}

	public function removeMenuTypeAction()
	{
		$menuType = $this->request->getPost('menuType', ['alphanum'], '');
		$params   = [
			'conditions' => 'context = :context: AND data = :menuType:',
			'bind'       => [
				'context'  => 'cms.menu.type',
				'menuType' => $menuType,
			],
		];

		if ($this->isValidRequest()
			&& !empty($menuType)
			&& ($entity = ConfigModel::findFirst($params))
			&& $entity->delete()
		)
		{
			if ($items = Menu::getMenuItems($menuType))
			{
				foreach ($items as $item)
				{
					if ($entity = ConfigModel::findFirst('id = ' . (int) $item->id))
					{
						$entity->delete();
					}
				}
			}

			return $this->response->setJsonContent(
				[
					'success' => true,
					'message' => null,
				]
			);
		}

		return $this->response->setJsonContent(
			[
				'success' => false,
				'message' => null,
			]
		);
	}

	protected function indexToolBar($activeState = null, $excludes = [])
	{

	}
}