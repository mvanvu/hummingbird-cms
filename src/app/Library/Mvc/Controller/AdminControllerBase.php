<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Mvc\Model;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\Toolbar;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\Config;
use MaiVu\Hummingbird\Lib\Helper\Form as FormHelper;
use MaiVu\Hummingbird\Lib\Helper\Date;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Mvc\Model\ModelBase;
use MaiVu\Hummingbird\Lib\Mvc\Model\Nested;
use MaiVu\Hummingbird\Lib\Mvc\Model\User as UserModel;
use MaiVu\Hummingbird\Lib\Form\FormsManager;
use MaiVu\Hummingbird\Lib\Form\Form;
use MaiVu\Hummingbird\Lib\Form\Field;
use MaiVu\Php\Registry;
use MaiVu\Php\Filter;
use Exception;

class AdminControllerBase extends ControllerBase
{
	/** @var ModelBase | null $model */
	public $model = null;

	/** @var string */
	public $pickedView = null;

	/** @var Uri */
	public $uri = null;

	/** @var string */
	public $dataKey = null;

	/** @var string */
	public $stateField = 'state';

	public function onConstruct()
	{
		$action = $this->dispatcher->getActionName();

		if (is_string($this->model))
		{
			if (null === $this->pickedView)
			{
				$this->pickedView = $this->model;
			}

			$modelClass = 'MaiVu\\Hummingbird\\Lib\\Mvc\\Model\\' . $this->model;

			if (class_exists($modelClass))
			{
				$id = (int) $this->request->getPost('id', ['int'], $this->dispatcher->getParam('id', 'int'));

				if ($id > 0
					&& ($entity = $modelClass::findFirst('id = ' . (int) $id))
				)
				{
					$this->model = $entity;
				}
				else
				{
					$this->model = new $modelClass;
				}
			}
		}

		if ($this->pickedView)
		{
			$this->view->pick($this->pickedView . '/' . ucfirst($action));
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

		$controller = str_replace('admin_', '', strtolower($this->dispatcher->getControllerName()));
		$this->uri  = Uri::getInstance(['uri' => $controller]);
		$this->prepareUri($this->uri);
		$this->view->setVars(
			[
				'uri'           => $this->uri,
				'model'         => $this->model,
				'disableNavbar' => in_array($action, ['add', 'edit']),
			]
		);

		if ($this->model->hasStandardMetadata()
			&& $this->model->isCheckedIn()
			&& !$this->canUnlock()
			&& in_array($action, ['edit', 'save', 'unlock', 'delete'])
		)
		{
			$this->flashSession->error(Text::_('err-unlock-denied'));

			return $this->response->redirect($this->uri->routeTo('index'), true);
		}

		$this->dataKey = $this->model->getIgnorePrefixModelName();

		if ($context = $this->dispatcher->getParam('context', ['trim', 'string']))
		{
			$this->dataKey .= $context;
		}

		parent::onConstruct();
		$this->adminBase();
	}

	protected function indexTitle()
	{
		$this->tag->setTitle(Text::_(str_replace('_', '-', $this->dispatcher->getControllerName()) . '-index-title'));
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

	public function indexAction()
	{
		$this->indexTitle();
		$this->persistent->set($this->dataKey . 'editData', null);
		$conditions   = [];
		$bindData     = [];
		$hasSearchBox = false;
		$filterData   = $this->request->get('FilterForm', null, []);

		if ($this->request->hasQuery('page'))
		{
			$this->persistent->set($this->dataKey . 'page', (int) $this->request->getQuery('page', ['int'], 1));
		}

		if (!$this->persistent->has($this->dataKey . 'page'))
		{
			$this->persistent->set($this->dataKey . 'page', 1);
		}

		$page = $this->persistent->get($this->dataKey . 'page');

		if ($this->request->isPost())
		{
			$sessionFilterData = $this->request->getPost('FilterForm', null, []);
		}
		else
		{
			$sessionFilterData = $this->persistent->get($this->dataKey . 'filters');
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
			if ($validFilterData = $filterForm->bind($filterData))
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

						$conditions[] = 'item.' . $fieldName . ' IN ({' . $fieldName . ':array})';
					}
					else
					{
						$conditions[] = 'item.' . $fieldName . ' = :' . $fieldName . ':';
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
						$orWhere[] = 'LOWER(item.' . $searchField . ') LIKE :search:';
					}

					$conditions[]       = count($orWhere) > 1 ? '(' . implode(' OR ', $orWhere) . ')' : $orWhere[0];
					$bindData['search'] = '%' . $sessionFilterData['search'] . '%';
				}
			}
		}

		$this->persistent->set($this->dataKey . 'filters', $sessionFilterData);
		$orderFields = $this->model->getOrderFields();
		$query       = $this->model->getModelsManager()
			->createBuilder()
			->from(['item' => get_class($this->model)]);

		if ($order = $this->request->get('_sort', ['string'], ''))
		{
			$parts = preg_split('/\s+/', $order, 2);

			if (in_array($parts[0], $orderFields))
			{
				$this->persistent->set($this->dataKey . 'orderField', $order);
			}
		}

		if (empty($order))
		{
			$order = $this->persistent->get($this->dataKey . 'orderField');

			if (empty($order) && isset($orderFields[0]))
			{
				$order = $orderFields[0] . ' ' . ($orderFields[0] === 'id' ? 'DESC' : 'ASC');
				$this->persistent->set($this->dataKey . 'orderField', $order);
			}
		}

		if ($bindData)
		{
			$query->where(implode(' AND ', $conditions), $bindData);
		}

		if (!empty($order))
		{
			$query->orderBy('item.' . trim($order));
		}

		$activeState = $this->stateField && isset($bindData[$this->stateField]) ? $bindData[$this->stateField] : null;

		if ($this->stateField
			&& (null === $activeState || '' === $activeState)
		)
		{
			$query->andWhere('item.' . $this->stateField . ' <> \'T\'');
		}

		if ($this->model instanceof Nested)
		{
			$page  = 0;
			$limit = 500;
		}
		else
		{
			$limit = Config::get('listLimit', 20);
		}

		$this->indexToolBar($activeState);
		$this->prepareIndexQuery($query);
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
						'searchValue'  => isset($filterData['search']) ? $filterData['search'] : null,
						'hasSearchBox' => $hasSearchBox,
						'activeFilter' => $activeFilter,
					]
				),
			]
		);
	}

	public function editAction()
	{
		$formsManager       = $this->model->getFormsManager();
		$paramsFormsManager = $this->model->getParamsFormsManager();
		$persistentData     = $this->model->toArray();
		$translationsData   = [];

		if ($this->model->id)
		{
			// Checkin this model
			$this->model->checkin();

			if (Language::isMultilingual())
			{
				foreach (Language::getExistsLanguages() as $langCode => $language)
				{
					if ($translations = $this->model->getTranslations($langCode, true))
					{
						foreach ($translations as $translation)
						{
							$translationsData[$translation['refField']][$langCode] = $translation['translatedValue'];
						}
					}
				}
			}
		}

		if ($data = $this->persistent->get($this->dataKey . 'editData'))
		{
			foreach ($data as $name => $value)
			{
				$persistentData[$name] = $value;
			}
		}

		$persistentData['id'] = $this->model->id;

		foreach ($formsManager->getForms() as $formName => $entityForm)
		{
			/** @var Form $entityForm */
			$entityForm->bind($persistentData);
			$entityForm->setFieldsTranslationData($translationsData);
		}

		if ($paramsFormsManager->count())
		{
			foreach ($paramsFormsManager->getForms() as $paramName => $paramForm)
			{
				/** @var Form $paramForm */
				$paramData              = isset($persistentData[$paramName]) ? Registry::parseData($persistentData[$paramName]) : [];
				$paramsTranslationsData = [];

				if (isset($translationsData[$paramName]))
				{
					foreach ($translationsData[$paramName] as $langCode => $translationsDatum)
					{
						foreach (Registry::parseData($translationsDatum) as $k => $v)
						{
							$paramsTranslationsData[$k][$langCode] = $v;
						}
					}
				}

				$paramForm->bind($paramData);
				$paramForm->setFieldsTranslationData($paramsTranslationsData);
			}

			$this->prepareParamsFormsManager($paramsFormsManager);
		}

		$this->prepareFormsManager($formsManager);
		$this->editTitle();
		$this->editToolBar();
		$this->view->setVars(
			[
				'model'              => $this->model,
				'formsManager'       => $formsManager,
				'paramsFormsManager' => $paramsFormsManager,
			]
		);
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

		return $this->response->redirect($redirect, true);
	}

	public function save2closeAction()
	{
		return $this->saveAction();
	}

	protected function saveTranslations(ModelBase $model, Form $form, array $rawData, $groupField = null)
	{
		$tranFields = [];

		foreach ($form->getFields() as $name => $field)
		{
			/** @var Field $field */

			if ($field->get('translate', false))
			{
				$tranFields[$name] = $field;
			}
		}

		$languages   = array_keys(Language::getExistsLanguages());
		$prefixTable = $this->modelsManager->getModelPrefix();
		$refTable    = preg_replace('/^' . preg_quote($prefixTable, '/') . '/', '', $model->getSource(), 1);
		$refKey      = 'id=' . $model->id;

		// Remove old translations
		/** @var Mysql $db */
		$db = $this->getDI()->get('db');
		static $removedTables = [];

		if (!in_array($refTable, $removedTables))
		{
			$removedTables[] = $refTable;
			$db->execute('DELETE FROM ' . $prefixTable . 'translations WHERE translationId LIKE :translationId',
				[
					'translationId' => '%.' . $refTable . '.' . $refKey . '.%',
				]
			);
		}

		$insertValues = [];
		$bindData     = [];
		$walk         = 0;

		if ($groupField)
		{
			if (property_exists($model, $groupField))
			{
				$originalData = $model->{$groupField};

				foreach ($languages as $language)
				{
					if (isset($rawData['translations'][$language][$groupField]))
					{
						$paramsData     = [];
						$tranParamsData = (array) $rawData['translations'][$language][$groupField];

						foreach ($tranParamsData as $fieldName => $value)
						{
							if (array_key_exists($fieldName, $tranFields))
							{
								$paramsData[$fieldName] = $tranFields[$fieldName]->applyFilters($value);
							}
						}

						$originalValue   = new Registry($originalData);
						$translatedValue = new Registry($originalData);
						$translatedValue->merge($paramsData);

						// Key data
						$k0 = 'translationId' . $walk;
						$k1 = 'originalValue' . $walk;
						$k2 = 'translatedValue' . $walk;
						$walk++;

						// Bind data
						$bindData[$k0] = $language . '.' . $refTable . '.' . $refKey . '.' . $groupField;
						$bindData[$k1] = $originalValue->toString();
						$bindData[$k2] = $translatedValue->toString();

						// Insert value
						$insertValues[] = '(:' . $k0 . ',:' . $k1 . ',:' . $k2 . ')';
					}
				}
			}
		}
		else
		{
			foreach ($rawData['translations'] as $langCode => $tranData)
			{
				if (in_array($langCode, $languages))
				{
					foreach ($tranData as $referenceField => $v)
					{
						if (array_key_exists($referenceField, $tranFields))
						{
							$translatedValue = $tranFields[$referenceField]->cleanValue($v);
							$originalValue   = $model->{$referenceField};

							if (is_array($translatedValue))
							{
								$translatedValue = json_encode($translatedValue);
							}

							if (is_array($originalValue))
							{
								$originalValue = json_encode($originalValue);
							}

							if (empty($translatedValue) || strcmp($originalValue, $translatedValue) === 0)
							{
								continue;
							}

							// Key data
							$k0 = 'translationId' . $walk;
							$k1 = 'originalValue' . $walk;
							$k2 = 'translatedValue' . $walk;
							$walk++;

							// Bind data
							$bindData[$k0] = $langCode . '.' . $refTable . '.' . $refKey . '.' . $referenceField;
							$bindData[$k1] = $originalValue;
							$bindData[$k2] = $translatedValue;

							// Insert value
							$insertValues[] = '(:' . $k0 . ',:' . $k1 . ',:' . $k2 . ')';
						}
					}
				}
			}
		}

		if ($insertValues)
		{
			$insertSql = 'INSERT INTO ' . $prefixTable . 'translations(translationId, originalValue, translatedValue)'
				. 'VALUES ' . implode(',', $insertValues);
			$db->execute($insertSql, $bindData);
		}
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

	protected function saveEntity(FormsManager $formsManager, &$redirect)
	{
		$isNew   = (int) $this->model->id < 1;
		$rawData = $this->request->getPost('FormData', null, []);
		$this->persistent->set($this->dataKey . 'editData', $rawData);
		$this->model->controllerBeforeBindData($rawData);
		$validData     = [];
		$errorMessages = [];

		/** @var Form $entityForm */
		foreach ($formsManager->getForms() as $entityForm)
		{
			if (false === ($filteredData = $entityForm->bind($rawData)) || !$entityForm->isValid())
			{
				$errorMessages = array_merge($errorMessages, $entityForm->getMessages());
			}
			else
			{
				$validData = array_merge($validData, $filteredData);
			}
		}

		if (count($errorMessages))
		{
			$this->flashSession->warning(implode('<br/>', $errorMessages));

			return false;
		}

		$paramsFormsManager = $this->model->getParamsFormsManager();

		if ($paramsFormsManager->count())
		{
			foreach ($paramsFormsManager->getForms() as $paramName => $paramForm)
			{
				/** @var Form $paramForm */
				$paramsData = $paramForm->bind(isset($rawData[$paramName]) ? $rawData[$paramName] : []);

				if (false === $paramsData || !$paramForm->isValid())
				{
					$this->flashSession->warning(implode('<br/>', $paramForm->getMessages()));

					return false;
				}

				$validData[$paramName] = $paramsData;
			}
		}

		if ($formsManager->has('metadata'))
		{
			$metadataForm = $formsManager->get('metadata');
			$validData    = array_merge($validData, $metadataForm->bind($rawData));

			if (!$metadataForm->isValid())
			{
				$this->flashSession->warning(implode('<br/>', $metadataForm->getMessages()));

				return false;
			}
		}

		try
		{
			$this->doBeforeSave($validData);
			$this->model->controllerDoBeforeSave($validData);

			if ($this->model->hasStandardMetadata())
			{
				$date   = Date::getInstance()->toSql();
				$userId = User::getInstance()->getEntity()->id;

				if ($isNew)
				{
					$validData['createdAt'] = $date;
					$validData['createdBy'] = $userId;
				}
				else
				{
					$validData['modifiedAt'] = $date;
					$validData['modifiedBy'] = $userId;
				}
			}

			$this->model->assign($validData);

			if ($this->model->save())
			{
				$this->flashSession->success(Text::_('item-saved'));
				$this->persistent->set($this->dataKey . 'editData', null);
				$translate = Language::isMultilingual() && !empty($rawData['translations']);

				// Update entity ID
				$redirect = $this->uri->routeTo('edit/' . $this->model->id);
				$this->doAfterSave($validData);
				$this->model->controllerDoAfterSave($validData);

				if ($translate)
				{
					foreach ($formsManager->getForms() as $entityForm)
					{
						$this->saveTranslations($this->model, $entityForm, $rawData);
					}

					if ($paramsFormsManager->count())
					{
						foreach ($paramsFormsManager->getForms() as $groupField => $paramForm)
						{
							$this->saveTranslations($this->model, $paramForm, $rawData, $groupField);
						}
					}
				}
			}
			else
			{
				$this->persistent->set($this->dataKey . 'editData', $validData);

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
			$this->response->redirect($redirect, true);

			return false;
		}

		return true;
	}

	public function saveAction()
	{
		if (!$this->request->isPost()
			|| !FormHelper::checkToken()
		)
		{
			return $this->redirectBack();
		}

		$formsManager = $this->model->getFormsManager();
		$this->prepareFormsManager($formsManager);
		$redirect = $this->uri->routeTo('edit/' . $this->model->id ?: 0);
		$this->saveEntity($formsManager, $redirect);

		if ($this->dispatcher->getActionName() === 'save2close')
		{
			return $this->closeAction();
		}

		return $this->response->redirect($redirect, true);
	}

	public function copyAction()
	{
		if (!$this->request->isPost())
		{
			return $this->redirectBack();
		}

		$cid   = $this->request->getPost('cid', null, []);
		$count = 0;

		foreach ($cid as $id)
		{
			$id = (int) $id;

			if ($id > 0
				&& ($entity = $this->model->findFirst('id = ' . $id))
			)
			{
				/** @var ModelBase $entity */
				if ($entity->copy())
				{
					$count++;
				}
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

		$this->response->redirect($this->uri->routeTo('/index'), true);
	}

	public function deleteAction()
	{
		if (!$this->request->isPost())
		{
			return $this->redirectBack();
		}

		$cid   = $this->request->getPost('cid', ['int'], []);
		$count = 0;

		foreach ($cid as $id)
		{
			$id = (int) $id;

			if ($id > 0
				&& ($entity = $this->model->findFirst('id = ' . $id))
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

		$this->response->redirect($this->uri->routeTo('/index'), true);
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
				&& $this->canUnlock($entity)
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

		$this->response->redirect($this->uri->routeTo('/index'), true);
	}

	protected function canUnlock(ModelBase $model = null)
	{
		$user = User::getInstance();

		if ($user->access('super'))
		{
			return true;
		}

		if (null === $model)
		{
			$model = $this->model;
		}

		if (($model instanceof UserModel)
			&& $model->id == $user->id
		)
		{
			return true;
		}

		return false;
	}

	protected function indexToolBar($activeState = null, $excludes = [])
	{
		$user = User::getInstance();

		if (!in_array('add', $excludes))
		{
			Toolbar::add('add', $this->uri->routeTo('/edit/0'), 'file-add');
		}

		if (!in_array('copy', $excludes))
		{
			Toolbar::add('copy', $this->uri->routeTo('/copy'), 'files');
		}

		if ($this->stateField && property_exists($this->model, $this->stateField))
		{
			if ($activeState === 'T')
			{
				if (!in_array('delete', $excludes))
				{
					Toolbar::add('delete', $this->uri->routeTo('/delete'), 'close');
				}
			}
			else
			{
				if (!in_array('trash', $excludes))
				{
					Toolbar::add('trash', $this->uri->routeTo('/status'), 'trash');
				}
			}
		}
		elseif (!in_array('delete', $excludes))
		{
			Toolbar::add('delete', $this->uri->routeTo('/delete'), 'close');
		}

		if (!in_array('unlock', $excludes)
			&& $this->model->hasStandardMetadata()
		)
		{
			Toolbar::add('unlock', $this->uri->routeTo('/unlock'), 'unlock');
		}

		if ($user->access('super')
			&& ($this->model instanceof Nested)
		)
		{
			Toolbar::add('rebuild', $this->uri->routeTo('/rebuild'), 'sort-amount-asc');
		}
	}

	protected function editToolBar()
	{
		$id = (int) $this->request->get('id', 'int', $this->dispatcher->getParam('id', 'int'));
		Toolbar::add('save', $this->uri->routeTo('/save/' . $id), 'cloud-check');
		Toolbar::add('save2close', $this->uri->routeTo('/save2close/' . $id), 'save');
		Toolbar::add('close', $this->uri->routeTo('/close/' . $id), 'close');
	}

	public function closeAction()
	{
		if ($this->canUnlock() && $this->model->isCheckedIn())
		{
			$this->model->checkout();
		}

		return $this->response->redirect($this->uri->routeTo('/index'), true);
	}

	public function orderAction()
	{
		if ($this->request->isAjax()
			&& $this->request->isPost()
			&& ($cid = $this->request->getPost('cid', null, []))
		)
		{
			$cid   = Filter::clean($cid, ['uint', 'unique']);
			$order = 0;

			foreach ($cid as $id)
			{
				if ($entity = $this->model->findFirst('id = ' . $id))
				{
					$entity->ordering = $order++;
					$entity->save();
				}
			}

			return $this->response->setJsonContent(['OK' => true]);
		}
	}

	public function statusAction()
	{
		$cid        = $this->request->getPost('cid', null, []);
		$postAction = $this->request->getPost('postAction', ['trim'], '');
		$entityId   = (int) $this->request->getPost('entityId', ['int'], 0);

		if (!$this->request->isPost() || !in_array($postAction, ['P', 'U', 'T']))
		{
			return $this->redirectBack();
		}

		if ($entityId > 0)
		{
			$cid[] = $entityId;
		}

		$count = 0;

		foreach (array_unique($cid) as $id)
		{
			$id = (int) $id;

			if ($id > 0
				&& ($entity = $this->model->findFirst('id = ' . $id))
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

		return $this->response->redirect($this->uri->routeTo('index'), true);
	}

	/**
	 * @return ModelBase|null
	 */
	public function getModel()
	{
		return $this->model;
	}

	protected function prepareUri(Uri $uri)
	{
		// Todo something
	}

	protected function prepareIndexQuery(BuilderInterface $query)
	{
		// Todo something
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		// Todo something
	}

	protected function prepareParamsFormsManager(FormsManager $paramsFormsManager)
	{
		// Todo something
	}

	protected function doBeforeSave(array &$validData)
	{
		// Todo something
	}

	protected function doAfterSave(array $validData)
	{
		// Todo something
	}
}
