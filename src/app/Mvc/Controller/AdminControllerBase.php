<?php

namespace App\Mvc\Controller;

use App\Helper\Config;
use App\Helper\Constant;
use App\Helper\Language;
use App\Helper\Service;
use App\Helper\Text;
use App\Helper\Toolbar;
use App\Helper\Uri;
use App\Mvc\Model\ModelBase;
use App\Mvc\Model\Nested;
use App\Traits\Hooker;
use App\Traits\Permission;
use Exception;
use MaiVu\Php\Filter;
use MaiVu\Php\Form\FormsManager;
use Phalcon\Db\Enum;
use Phalcon\Mvc\Model;
use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;

class AdminControllerBase extends ControllerBase
{
	/**
	 * @var ModelBase | string | null
	 */
	public $model = null;

	/**
	 * @var string
	 */
	public $pickedView = null;

	/**
	 * @var string
	 */
	public $renderView = null;

	/**
	 * @var Uri
	 */
	public $uri = null;

	/**
	 * @var string
	 */
	public $dataKey = null;

	/**
	 * @var string
	 */
	public $mainEditFormName = null;

	/**
	 * @var string
	 */
	public $stateField = 'state';

	use Permission;
	use Hooker;

	public function onConstruct()
	{
		$action = $this->dispatcher->getActionName();

		if (is_string($this->model))
		{
			if (null === $this->pickedView)
			{
				$this->pickedView = $this->model;
			}

			$modelClass = Constant::NAMESPACE_MODEL . '\\' . $this->model;

			if (class_exists($modelClass))
			{
				$id          = (int) $this->request->getPost('id', ['int'], $this->dispatcher->getParam('id', 'int'));
				$this->model = ModelBase::getInstance($id, $modelClass);
			}
		}

		if ($this->pickedView)
		{
			$ucAction         = ucfirst($action);
			$this->renderView = $this->pickedView . '/' . $ucAction;
			$found            = false;

			foreach ($this->view->getViewsDir() as $viewDir)
			{
				if (is_file($viewDir . $this->renderView . '.volt'))
				{
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				$this->renderView = 'Administrator/' . $ucAction;
			}

			$this->view->pick($this->renderView);
		}

		if (!$this->model instanceof Model)
		{
			$this->dispatcher->forward(
				[
					'controller' => 'admin_error',
					'action'     => 'show'
				]
			);

			return false;
		}

		if ($this->model->hasStandardMetadata()
			&& $this->model->isCheckedIn()
			&& !$this->model->canUnlock()
			&& in_array($action, ['edit', 'save', 'unlock', 'delete'])
		)
		{
			$this->flashSession->error(Text::_('err-unlock-denied'));

			return $this->uri::redirect($this->uri->routeTo('index'));
		}

		$this->dataKey = $this->model->getIgnorePrefixModelName();

		if (empty($this->mainEditFormName))
		{
			$this->mainEditFormName = $this->dataKey;
		}

		if ($context = $this->dispatcher->getParam('context', ['trim', 'string']))
		{
			$this->dataKey .= $context;
		}

		$controller = str_replace('admin_', '', strtolower($this->dispatcher->getControllerName()));
		$this->uri  = Uri::getInstance(['uri' => $controller]);
		$this->callback('prepareUri', [$this->uri]);
		$this->view->setVars(
			[
				'uri'           => $this->uri,
				'model'         => $this->model,
				'disableNavbar' => in_array($action, ['add', 'edit']),
			]
		);

		parent::onConstruct();
		Text::script('confirm-delete-items');
		Text::script('select-items-first');
	}

	public function indexAction()
	{
		$this->indexTitle();
		$this->persistent->set($this->dataKey . '.editData', null);
		$filterData   = $this->request->get('filters', null, []);
		$conditions   = [];
		$bindData     = [];
		$hasSearchBox = false;

		if ($this->request->hasQuery('page'))
		{
			$this->persistent->set($this->dataKey . '.page', (int) $this->request->getQuery('page', ['int'], 1));
		}

		if (!$this->persistent->has($this->dataKey . '.page'))
		{
			$this->persistent->set($this->dataKey . '.page', 1);
		}

		$page = $this->persistent->get($this->dataKey . '.page');

		if ($this->request->isPost())
		{
			$sessionFilterData = $this->request->getPost('filters', null, []);
		}
		else
		{
			$sessionFilterData = $this->persistent->get($this->dataKey . '.filters');
		}

		if ($sessionFilterData)
		{
			foreach ($sessionFilterData as $k => $v)
			{
				if (!isset($filterData[$k]))
				{
					$filterData[$k] = $v;
				}
			}
		}
		else
		{
			$sessionFilterData = [];
		}

		$activeFilter = false;
		$filterForm   = $this->model->getFilterForm();

		if ($filterForm->count())
		{
			if ($validFilterData = $filterForm->bind(['filters' => $filterData])->toArray())
			{
				foreach ($validFilterData as $fieldName => $fieldValue)
				{
					if ('' === $fieldValue || null === $fieldValue)
					{
						unset($sessionFilterData[$fieldName]);
						continue;
					}

					if (is_array($fieldValue))
					{
						if (empty($fieldValue))
						{
							unset($sessionFilterData[$fieldName]);
							continue;
						}

						$conditions[] = 'item.[' . $fieldName . '] IN ({' . $fieldName . ':array})';
					}
					else
					{
						$conditions[] = 'item.[' . $fieldName . '] = :' . $fieldName . ':';
					}

					$activeFilter         = true;
					$bindData[$fieldName] = $fieldValue;
				}

				$sessionFilterData = array_merge($sessionFilterData, $validFilterData);
			}
		}

		if ($searchFields = $this->model->getSearchFields())
		{
			$hasSearchBox = true;

			if (empty($filterData['search']))
			{
				unset($sessionFilterData['search']);
			}
			else
			{
				$sessionFilterData['search'] = strtolower(Filter::clean($filterData['search'], ['string', 'trim']));

				if (strpos($sessionFilterData['search'], 'id:') === 0)
				{
					$conditions[]   = 'item.id = :id:';
					$bindData['id'] = (int) substr($sessionFilterData['search'], 3);
				}
				else
				{
					$orWhere = [];

					foreach ($searchFields as $searchField)
					{
						$orWhere[] = 'LOWER(item.[' . $searchField . ']) LIKE :search:';
					}

					$conditions[]       = count($orWhere) > 1 ? '(' . implode(' OR ', $orWhere) . ')' : $orWhere[0];
					$bindData['search'] = '%' . $sessionFilterData['search'] . '%';
				}
			}
		}

		if (isset($sessionFilterData['limit']))
		{
			$sessionFilterData['limit'] = abs((int) $sessionFilterData['limit']);

			if ($sessionFilterData['limit'] < 5 || $sessionFilterData['limit'] > 200)
			{
				$sessionFilterData['limit'] = Config::get('listLimit', 15);
			}
		}

		$this->persistent->set($this->dataKey . '.filters', $sessionFilterData);
		$orderFields = $this->model->getOrderFields();
		$query       = $this->model->getModelsManager()
			->createBuilder()
			->from(['item' => get_class($this->model)]);

		if ($order = $this->request->get('_sort', ['string'], ''))
		{
			$parts = preg_split('/\s+/', $order, 2);

			if (in_array($parts[0], $orderFields))
			{
				$this->persistent->set($this->dataKey . '.orderField', $order);
			}
		}

		if (empty($order))
		{
			$order = $this->persistent->get($this->dataKey . '.orderField');

			if (empty($order) && isset($orderFields[0]))
			{
				$order = $orderFields[0] . ' ' . ($orderFields[0] === 'id' ? 'DESC' : 'ASC');
				$this->persistent->set($this->dataKey . '.orderField', $order);
			}
		}

		if ($bindData)
		{
			$query->where(implode(' AND ', $conditions), $bindData);
		}

		if (!empty($order))
		{
			list($ordering, $direction) = explode(' ', $order);
			$query->orderBy('item.[' . $ordering . '] ' . $direction);
		}

		$activeState = $this->stateField && isset($bindData[$this->stateField]) ? $bindData[$this->stateField] : null;

		if ($this->stateField && (null === $activeState || '' === $activeState))
		{
			$query->andWhere('item.[' . $this->stateField . '] <> \'T\'');
		}

		if ($this->model instanceof Nested)
		{
			$page  = 0;
			$limit = 150;
			$query->andWhere(
				'item.id <> :rootId:',
				[
					'rootId' => $this->model->getRootId(),
				]
			)->orderBy('item.lft');
		}
		else
		{
			$limit = $sessionFilterData['limit'] ?? Config::get('listLimit', 15);
		}

		if ($this->request->isMethod('POST') && !$this->request->isAjax())
		{
			return $this->uri::redirect($this->uri->routeTo('index'));
		}

		$this->indexToolBar($activeState);
		$this->callback('prepareIndexQuery', [$query]);
		$this->view->setVars(
			[
				'model'        => $this->model,
				'activeOrder'  => $order,
				'activeFilter' => $activeFilter,
				'orderFields'  => $orderFields,
				'paginator'    => new Paginator(
					[
						'builder' => $query,
						'page'    => $page,
						'limit'   => $limit,
					]
				),
				'searchTools'  => $this->view->getPartial('Grid/SearchTools',
					[
						'filterForm'   => $filterForm,
						'limit'        => $limit,
						'searchValue'  => isset($filterData['search']) ? $filterData['search'] : null,
						'hasSearchBox' => $hasSearchBox,
						'activeFilter' => $activeFilter,
					]
				),
			]
		);
	}

	protected function indexTitle()
	{
		$this->tag->setTitle(Text::_(str_replace('_', '-', $this->dispatcher->getControllerName()) . '-index-title'));
	}

	protected function indexToolBar($activeState = null, $excludes = [])
	{
		if (!in_array('add', $excludes) && $this->model->canCreate())
		{
			Toolbar::add('add', $this->uri->routeTo('edit/0'), 'file-add');
		}

		if (!in_array('copy', $excludes) && $this->model->canCreate())
		{
			Toolbar::add('copy', $this->uri->routeTo('copy'), 'files');
		}

		if ($this->stateField && property_exists($this->model, $this->stateField))
		{
			if ($activeState === 'T')
			{
				if (!in_array('delete', $excludes) && $this->model->canDelete())
				{
					Toolbar::add('delete', $this->uri->routeTo('delete'), 'close');
				}
			}
			else
			{
				if (!in_array('trash', $excludes) && $this->model->canEditState())
				{
					Toolbar::add('trash', $this->uri->routeTo('status'), 'trash');
				}
			}
		}
		elseif (!in_array('delete', $excludes) && $this->model->canDelete())
		{
			Toolbar::add('delete', $this->uri->routeTo('delete'), 'close');
		}

		if (!in_array('unlock', $excludes) && $this->model->hasStandardMetadata())
		{
			Toolbar::add('unlock', $this->uri->routeTo('unlock'), 'unlock');
		}

		if ($this->model instanceof Nested && $this->user()->is('super'))
		{
			Toolbar::add('rebuild', $this->uri->routeTo('rebuild'), 'sort-amount-asc');
		}
	}

	public function editAction()
	{
		$formsManager   = $this->model->getFormsManager();
		$persistentData = [$this->mainEditFormName => $this->model->toArray()];

		if ($persistentData[$this->mainEditFormName]['id'])
		{
			// Checkin this model
			$this->model->checkin();
			$persistentData[$this->mainEditFormName]['i18n'] = $this->model->getI18nData();
		}

		if ($data = $this->persistent->get($this->dataKey . '.editData', []))
		{
			$persistentData = array_merge($persistentData, $data);
		}

		$formsManager->bind($persistentData);
		$this->callback('prepareFormsManager', [$formsManager]);
		$this->editTitle();
		$this->editToolBar();
		$this->view->setVars(
			[
				'model'        => $this->model,
				'formsManager' => $formsManager,
				'general'      => $this->mainEditFormName,
			]
		);
	}

	protected function editTitle()
	{
		if ($this->model->id)
		{
			if ($titleField = $this->model->getTitleField())
			{
				$placeholders = [
					$titleField => $this->model->{$titleField},
				];
			}
			else
			{
				$placeholders = null;
			}

			$this->tag->setTitle(Text::_(str_replace('_', '-', $this->dispatcher->getControllerName()) . '-edit-title', $placeholders));
		}
		else
		{
			$this->tag->setTitle(Text::_(str_replace('_', '-', $this->dispatcher->getControllerName()) . '-add-title'));
		}
	}

	protected function editToolBar()
	{
		$id = (int) $this->request->get('id', 'int', $this->dispatcher->getParam('id', 'int'));
		Toolbar::add('save', $this->uri->routeTo('/save/' . $id), 'cloud-check');
		Toolbar::add('save2close', $this->uri->routeTo('/save2close/' . $id), 'save');
		Toolbar::add('close', $this->uri->routeTo('/close/' . $id), 'close');
	}

	public function save2closeAction()
	{
		return $this->saveAction();
	}

	public function saveAction()
	{
		if (!$this->request->isPost())
		{
			return $this->redirectBack();
		}

		$formsManager = $this->model->getFormsManager();
		$this->callback('prepareFormsManager', [$formsManager]);
		$redirect = $this->uri->routeTo('edit/' . $this->model->id ?: 0);
		$this->saveEntity($formsManager, $redirect);

		if ($this->dispatcher->getActionName() === 'save2close')
		{
			return $this->closeAction();
		}

		return $this->uri::redirect($redirect);
	}

	protected function redirectBack()
	{
		$action = $this->dispatcher->getActionName();

		if ('save' === $action)
		{
			$action = 'edit';
		}

		if (in_array($action, ['status', 'close', 'trash', 'copy', 'delete', 'unlock', 'rebuild']))
		{
			$redirect = $this->uri->routeTo('index');
		}
		else
		{
			$redirect = $this->uri->routeTo($action . '/' . (int) $this->model->id);
		}

		return $this->uri::redirect($redirect);
	}

	protected function saveEntity(FormsManager $formsManager, &$redirect)
	{
		$isNew   = (int) $this->model->id < 1;
		$rawData = $this->request->getPost();
		$this->persistent->set($this->dataKey . '.editData', $rawData);
		$this->model->callback('controllerBeforeBindData', [&$rawData]);

		if (!$formsManager->isValid($rawData))
		{
			$this->flashSession->error(Text::_('cannot-save-item-msg'));

			return false;
		}

		$validFormData = $formsManager->getData(true);
		$validData     = $validFormData[$this->mainEditFormName] ?? [];

		try
		{
			$this->callback('doBeforeSave', [&$validData, $isNew]);
			$this->model->callback('controllerDoBeforeSave', [&$validData, $isNew]);

			if ($this->model->assign($validData)->save())
			{
				$this->flashSession->success(Text::_('item-saved'));
				$this->persistent->set($this->dataKey . '.editData', null);

				// Update entity ID
				$redirect        = $this->uri->routeTo('edit/' . $this->model->id);
				$validData['id'] = $this->model->id;
				$this->saveTranslations($formsManager);
				$this->callback('doAfterSave', [$validData, $isNew]);
				$this->model->callback('controllerDoAfterSave', [$validData, $isNew]);
			}
			else
			{
				$this->persistent->set($this->dataKey . '.editData', $validFormData);

				foreach ($this->model->getMessages() as $message)
				{
					$this->flashSession->error($message);
				}

				return false;
			}

		}
		catch (Exception $e)
		{
			$this->flashSession->warning($e->getMessage());
			$this->uri::redirect($redirect);

			return false;
		}

		return true;
	}

	protected function saveTranslations(FormsManager $formsManager)
	{
		if (!Language::isMultilingual() || (int) $this->model->id < 1)
		{
			return;
		}

		$prefixTable = $this->modelsManager->getModelPrefix();
		$refTable    = preg_replace('/^' . preg_quote($prefixTable, '/') . '/', '', $this->model->getSource(), 1);
		$refKey      = 'id=' . $this->model->id;

		// Remove old translations
		$db = Service::db();
		$db->execute('DELETE FROM ' . $prefixTable . 'translations WHERE translationId LIKE :translationId',
			[
				'translationId' => '%.' . $refTable . '.' . $refKey,
			]
		);

		$insertValues = [];
		$bindData     = [];
		$walk         = 0;

		foreach ($formsManager->getI18nData(true) as $language => $i18n)
		{
			$translatedValue = $i18n[$this->mainEditFormName] ?? $i18n ?? [];

			if (is_array($translatedValue) || is_object($translatedValue))
			{
				$translatedValue = json_encode($translatedValue);
			}

			if (empty($translatedValue))
			{
				continue;
			}

			// Key data
			$k0 = 'translationId' . $walk;
			$k1 = 'translatedValue' . $walk;
			$walk++;

			// Bind data
			$bindData[$k0] = $language . '.' . $refTable . '.' . $refKey;
			$bindData[$k1] = $translatedValue;

			// Insert value
			$insertValues[] = '(:' . $k0 . ',:' . $k1 . ')';
		}

		if ($insertValues)
		{
			$insertSql = 'INSERT INTO ' . $prefixTable . 'translations(translationId, translatedValue)'
				. 'VALUES ' . implode(',', $insertValues);
			$db->execute($insertSql, $bindData);
		}
	}

	public function closeAction()
	{
		if ($this->model->isCheckedIn() && $this->model->canUnlock())
		{
			$this->model->checkout();
		}

		return $this->uri::redirect($this->uri->routeTo('/index'));
	}

	public function copyAction()
	{
		if (!$this->request->isPost())
		{
			return $this->redirectBack();
		}

		$cid   = $this->request->getPost('cid', null, []);
		$count = 0;
		/** @var ModelBase $entity */

		foreach ($cid as $id)
		{
			$id = (int) $id;

			if ($id > 0
				&& ($entity = $this->model->findFirst($id))
				&& $entity->copy()
			)
			{
				$count++;
			}
		}

		if ($count)
		{
			$this->flashSession->success(Text::_('item-' . ($count > 1 ? 's' : '1') . '-copied', ['count' => $count]));
		}
		else
		{
			$this->flashSession->warning(Text::_('item-0-copied'));
		}

		return $this->uri::redirect($this->uri->routeTo('/index'));
	}

	public function deleteAction()
	{
		if (!$this->request->isPost())
		{
			return $this->redirectBack();
		}

		$cid   = $this->request->getPost('cid', null, []);
		$count = 0;

		foreach ($cid as $id)
		{
			if (!empty($id)
				&& ($entity = $this->model->findFirst(['id = :id:', 'bind' => ['id' => $id]]))
				&& (!property_exists($entity, 'state') || $entity->state === 'T')
				&& $entity->delete()
			)
			{
				$count++;
			}
		}

		if ($count)
		{
			$this->flashSession->success(Text::_('item-' . ($count > 1 ? 's' : '1') . '-deleted', ['count' => $count]));
		}
		else
		{
			$this->flashSession->warning(Text::_('item-0-deleted'));
		}

		return $this->uri::redirect($this->uri->routeTo('/index'));
	}

	public function unlockAction()
	{
		if (!$this->request->isPost())
		{
			return $this->redirectBack();
		}

		$cid   = $this->request->getPost('cid', [], 'array');
		$count = 0;

		foreach ($cid as $id)
		{
			$id = (int) $id;

			if ($id > 0
				&& ($entity = $this->model->findFirst('id = ' . $id))
				&& $entity->canUnlock()
				&& $entity->checkout()
			)
			{
				$count++;
			}
		}

		if ($count)
		{
			$this->flashSession->success(Text::_('item-' . ($count > 1 ? 's' : '1') . '-unlocked', ['count' => $count]));
		}
		else
		{
			$this->flashSession->warning(Text::_('item-0-unlocked'));
		}

		return $this->uri::redirect($this->uri->routeTo('/index'));
	}

	public function statusAction()
	{
		$cid        = $this->request->getPost('cid', null, []);
		$postAction = $this->request->getPost('postAction', ['trim'], '');
		$entityId   = $this->request->getPost('entityId', null, 0);

		if (!$this->request->isPost() || !in_array($postAction, ['P', 'U', 'T']))
		{
			return $this->redirectBack();
		}

		if (!empty($entityId))
		{
			$cid[] = $entityId;
		}

		$count = 0;

		foreach (array_unique($cid) as $id)
		{
			if (($entity = $this->model->findFirst(['id = :id:', 'bind' => ['id' => $id]]))
				&& property_exists($entity, $this->stateField)
				&& $entity->assign([$this->stateField => $postAction])->save()
			)
			{
				$count++;
			}
		}

		$statusMaps = [
			'U' => '-unpublished',
			'P' => '-published',
			'T' => '-trashed',
		];
		$suffix     = $statusMaps[$postAction];

		if ($count)
		{
			$this->flashSession->success(Text::_('item-' . ($count > 1 ? 's' : '1') . $suffix, ['count' => $count]));
		}
		else
		{
			$this->flashSession->warning(Text::_('item-0' . $suffix));
		}

		return $this->uri::redirect($this->uri->routeTo('index'));
	}

	/**
	 * @return ModelBase|null
	 */
	public function getModel()
	{
		return $this->model;
	}

	public function reorderAction()
	{
		$db       = Service::db();
		$table    = $this->model->getSource();
		$id       = Filter::clean($this->request->getPost('id'), ['uint']);
		$ordering = Filter::clean($this->request->getPost('ordering'), ['uint']);
		$cid      = Filter::clean($this->request->getPost('cid'), ['uint:array', 'unique']);
		$count    = 0;

		if ($ordering > 0 && $id > 0)
		{
			$db->execute(
				'UPDATE ' . $table . ' SET ordering = :ordering WHERE id = :id',
				[
					'ordering' => $ordering,
					'id'       => $id,
				]
			);
		}
		elseif ($cid)
		{
			$direction = strtoupper($this->request->getPost('direction', null, ''));

			if (!in_array($direction, ['ASC', 'DESC']))
			{
				$direction = 'ASC';
			}

			$items = $db->fetchAll(
				'SELECT ordering FROM ' . $table . ' WHERE id IN (' . implode(',', $cid) . ') ORDER BY ordering ' . $direction,
				Enum::FETCH_ASSOC
			);

			if ($items)
			{
				foreach ($cid as $id)
				{
					$item = array_shift($items);

					if ($id && $item)
					{
						$count++;
						$db->execute(
							'UPDATE ' . $table . ' SET ordering = :ordering WHERE id = :id',
							[
								'ordering' => $item['ordering'],
								'id'       => $id,
							]
						);
					}
				}
			}
		}

		return $this->response->setJsonContent($count);
	}

	protected function saveReference(ModelBase $entity, $validData)
	{
		$entity->assign($validData);

		if ($entity->save())
		{
			return true;
		}

		foreach ($entity->getMessages() as $message)
		{
			$this->flashSession->warning($message);
		}

		return false;
	}
}
