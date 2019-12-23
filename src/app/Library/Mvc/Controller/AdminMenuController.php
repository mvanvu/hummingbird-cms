<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Db\Adapter\Pdo\Mysql;
use MaiVu\Hummingbird\Lib\Helper\Asset;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Helper\Menu;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Helper\Form as FormHelper;
use MaiVu\Hummingbird\Lib\Mvc\Model\Config as ConfigModel;
use MaiVu\Hummingbird\Lib\Mvc\Model\Translation;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Form\Form;
use MaiVu\Hummingbird\Lib\Form\Field\CmsIcon;
use MaiVu\Php\Registry;
use MaiVu\Php\Filter;

class AdminMenuController extends AdminControllerBase
{
	/** @var ConfigModel $model */
	public $model = 'Config';

	/** @var string */
	public $pickedView = 'Menu';

	/** @var null */
	public $stateField = null;

	protected function isValidRequest()
	{
		if ($this->request->isAjax()
			&& $this->request->isPost()
			&& FormHelper::checkToken()
			&& User::getInstance()->access('super')
		)
		{
			return true;
		}

		return false;
	}

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

		Asset::addFiles(
			[
				'jquery.nestable.css',
				'jquery.nestable.js',
				'menu.js',
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

	protected function indexToolBar($activeState = null, $excludes = [])
	{
		return;
	}

	protected function getMenuForm()
	{
		$menus        = Menu::getRegisteredMenus();
		$itemsOptions = [];

		foreach ($menus as $type => $item)
		{
			$itemsOptions[$type] = $type;
		}

		return new Form('FormData',
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
					'filters'   => ['string', 'trim'],
				],
				[
					'name'    => 'icon',
					'type'    => 'CmsIcon',
					'filters' => ['html', 'trim'],
				],
				[
					'name'     => 'menu',
					'type'     => 'CmsMenuType',
					'required' => true,
					'rules'    => ['options'],
				],
				[
					'name'     => 'type',
					'type'     => 'Select',
					'required' => true,
					'options'  => $itemsOptions,
					'rules'    => ['options'],
				],
				[
					'name'    => 'target',
					'type'    => 'Select',
					'label'   => 'menu-target',
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
					'options' => [
						'Y' => 'yes',
						'N' => 'no',
					],
					'rules'   => ['options'],
				],
			]
		);
	}

	public function createMenuAction()
	{
		if ($this->isValidRequest())
		{
			$formData = $this->request->getPost('FormData', null, []);
			$menuForm = $this->getMenuForm();

			if (!$menuForm->isValid($formData))
			{
				return $this->response->setJsonContent(
					[
						'success' => false,
						'message' => implode('<br/>', $menuForm->getMessages()),
					]
				);
			}

			$validData            = $menuForm->getData()->toArray();
			$menus                = Menu::getRegisteredMenus();
			$menuConfig           = $menus[$validData['type']];
			$menuData             = $validData;
			$menuData['params']   = [];
			$menuData['parentId'] = 0;
			$menuParamsForm       = new Form('FormData.params', $menuConfig['params']);

			if (isset($menuConfig['params']))
			{
				$paramsData = isset($formData['params']) ? $formData['params'] : [];

				if (!$menuParamsForm->isValid($paramsData))
				{
					return $this->response->setJsonContent(
						[
							'success' => false,
							'message' => implode('<br/>', $menuParamsForm->getMessages()),
						]
					);
				}

				$menuData['params'] = $menuParamsForm->getData()->toArray();
			}

			if ($menuData['id'] > 0
				&& ($menuItemEntity = ConfigModel::findFirst('id = ' . $menuData['id']))
				&& $menuItemEntity->context === 'cms.menu.item'
			)
			{
				$isNew      = false;
				$entityData = json_decode($menuItemEntity->data, true) ?: [];

				if (isset($entityData['parentId']))
				{
					$menuData['parentId'] = (int) $entityData['parentId'];
				}
			}
			else
			{
				$menuItemEntity           = new ConfigModel;
				$menuItemEntity->context  = 'cms.menu.item';
				$menuItemEntity->ordering = 0;
				$isNew                    = true;
			}

			$menuItemEntity->data = json_encode($menuData);

			if ($menuItemEntity->save())
			{
				if (Language::isMultilingual())
				{
					/** @var Mysql $db */
					$db          = $this->getDI()->get('db');
					$prefixTable = $this->modelsManager->getModelPrefix();
					$db->execute('DELETE FROM ' . $prefixTable . 'translations WHERE translationId LIKE :translationId',
						[
							'translationId' => '%.config_data.id=' . $menuItemEntity->id . '.%',
						]
					);

					if (!empty($formData['translations']))
					{
						if ($menuParamsForm->count())
						{
							foreach ($menuParamsForm->getFields() as $name => $field)
							{
								if (!$field->get('translate'))
								{
									$menuParamsForm->remove($name);
								}
							}
						}

						$hasParams   = $menuParamsForm->count() > 0;
						$originValue = json_encode($menuData);

						foreach ($formData['translations'] as $langCode => $langData)
						{
							$transData = [];

							if (!empty($langData['title']))
							{
								$title = trim(Filter::clean($langData['title'], 'string'));

								if ($title !== $menuData['title'])
								{
									$transData['title'] = $title;
								}
							}

							if ($hasParams && isset($langData['params']))
							{
								$menuParamsForm->bind($langData['params']);

								foreach ($menuParamsForm->getFields() as $name => $field)
								{
									if ($field->isValid())
									{
										$value = $field->getValue();

										if (!empty($value) && $value !== $transData['params'][$name])
										{
											$transData['params'][$name] = $field->getValue();
										}
									}
								}
							}

							if ($transData)
							{
								$translatedValue = json_encode($transData);
								$insertSql       = 'INSERT INTO ' . $prefixTable . 'translations(translationId,originalValue,translatedValue)'
									. ' VALUES (:translationId,:originalValue,:translatedValue)';
								$db->execute($insertSql,
									[
										'translationId'   => $langCode . '.config_data.id=' . $menuItemEntity->id . '.data',
										'originalValue'   => $originValue,
										'translatedValue' => $translatedValue,
									]
								);
							}
						}
					}
				}

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
					'message' => $this->getErrorMessage($menu),
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

	public function nestableItemsAction()
	{
		if ($this->isValidRequest())
		{
			$items = $this->request->getPost('items', null, []);
			$this->handleNestableItems($items);

			return $this->response->setJsonContent('OK');
		}
	}

	public function itemAction()
	{
		$type  = $this->request->get('type', ['trim', 'string'], '');
		$id    = (int) $this->request->get('id', ['absint'], 0);
		$menus = Menu::getRegisteredMenus();

		if (isset($menus[$type]))
		{
			$menuForm = $this->getMenuForm();
			$menuForm->getField('id')->setValue($id);
			$menuForm->getField('type')->setValue($type);
			$menuForm->getField('menu')->setValue($this->persistent->get('admin.menu.type'));
			$paramsForm = new Form('FormData.params');

			if (isset($menus[$type]['params']))
			{
				$paramsForm->load($menus[$type]['params']);
			}

			if ($id > 0
				&& ($item = ConfigModel::findFirst('id = ' . $id))
			)
			{
				$registry = new Registry($item->data);
				$transFormData   = [];
				$transParamsData = [];

				if (Language::isMultilingual())
				{
					$transData = Translation::find(
						[
							'conditions' => 'translationId LIKE :translationId:',
							'bind'       => [
								'translationId' => '%.config_data.id=' . $id . '.data',
							],
						]
					);

					if ($transData->count())
					{
						foreach ($transData as $transDatum)
						{
							$parsedData = $registry->parse($transDatum->translatedValue);
							$parts      = explode('.', $transDatum->translationId);
							$langCode   = $parts[0];

							foreach ($parsedData as $name => $value)
							{
								if ('params' === $name)
								{
									foreach ($value as $k => $v)
									{
										$transParamsData[$k][$langCode] = $v;
									}
								}
								else
								{
									$transFormData[$name][$langCode] = $value;
								}
							}
						}
					}
				}

				$menuForm->bind($registry, $transFormData);
				$paramsForm->bind($registry->get('params', []), $transParamsData);
			}

			$this->view->setMainView('Raw');
			$this->view->setVars(
				[
					'id'         => $id,
					'type'       => $type,
					'menuForm'   => $menuForm,
					'paramsForm' => $paramsForm,
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
					&& $entity->save(['data' => $newName])
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
								$entity->save(['data' => $data]);
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
}