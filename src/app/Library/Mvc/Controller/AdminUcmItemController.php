<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use MaiVu\Hummingbird\Lib\Factory;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Helper\Asset;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\Event as EventHelper;
use MaiVu\Hummingbird\Lib\Helper\UcmItem as UcmItemHelper;
use MaiVu\Hummingbird\Lib\Helper\UcmField;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Helper\Form;
use MaiVu\Hummingbird\Lib\Helper\Nested as NestedHelper;
use MaiVu\Hummingbird\Lib\Mvc\Model\Nested;
use MaiVu\Hummingbird\Lib\Mvc\Model\UcmItem;
use MaiVu\Hummingbird\Lib\Form\Form as FormManager;
use MaiVu\Hummingbird\Lib\Form\FormsManager;
use MaiVu\Hummingbird\Lib\Form\Field;
use Exception;

class AdminUcmItemController extends AdminControllerBase
{
	/** @var UcmItem */
	public $model = 'UcmItem';

	/** @var string */
	public $pickedView = 'UcmItem';

	/** @var string */
	public $context;

	/** @var string */
	public $contextAlias;

	public function onConstruct()
	{
		$this->context      = $this->dispatcher->getParam('context');
		$this->contextAlias = UcmItemHelper::prepareContext($this->context);
		$this->model        = $this->contextAlias;
		EventHelper::trigger('onBeforeUcm' . $this->contextAlias . 'PrepareModel', [$this], ['Cms']);
		EventHelper::trigger('onBeforeUcm' . $this->contextAlias . ucfirst($this->dispatcher->getActionName()), [$this], ['Cms']);
		parent::onConstruct();
	}

	protected function prepareIndexQuery(BuilderInterface $query)
	{
		if ($this->model instanceof Nested)
		{
			$query->andWhere(
				'item.id <> :rootId:',
				[
					'rootId' => $this->model->getRootId(),
				]
			)->orderBy('item.lft');
		}

		$results = EventHelper::trigger('beforeUcm' . $this->contextAlias . 'PrepareIndexQuery', [$this, $query], ['Cms']);

		if (!in_array(false, $results, true))
		{
			$query->andWhere('item.context = :context:', ['context' => $this->context]);
		}
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$formsManager->get('general')->getField('context')->setValue($this->context);
		$this->model->prepareFormsManager($formsManager);
	}

	protected function prepareUri(Uri $uri)
	{
		$baseUri = 'content/' . $this->context;
		$uri->setBaseUri($baseUri)
			->setVar('uri', $baseUri);
	}

	protected function indexTitle()
	{
		$this->tag->setTitle(Text::_($this->context . '-admin-index-title'));
	}

	protected function editTitle()
	{
		if ($this->model->id)
		{
			if ($title = $this->model->getTitleField())
			{
				$placeholders = [
					'title' => $this->model->{$title},
				];
			}
			else
			{
				$placeholders = null;
			}

			$this->tag->setTitle(Text::_($this->context . '-admin-edit-title', $placeholders));
		}
		else
		{
			$this->tag->setTitle(Text::_($this->context . '-admin-add-title'));
		}
	}

	protected function handleNested()
	{
		if ($this->model instanceof Nested)
		{
			Asset::addFiles(
				[
					'jquery.nestable.css',
					'jquery.nestable.js',
					'nested.js',
				]
			);

			$paginate = $this->view->getVar('paginator')->paginate();
			$this->view->pick($this->pickedView . '/Nested');
			$this->view->setVars(
				[
					'paginate'     => $paginate,
					'nestedHelper' => new NestedHelper($this->uri, $paginate),
				]
			);
		}
	}

	public function indexAction()
	{
		parent::indexAction();
		$this->handleNested();
	}

	public function rebuildAction()
	{
		if ($this->request->isPost()
			&& ($this->model instanceof Nested)
			&& User::getInstance()->access('super')
			&& Form::checkToken()
		)
		{
			$this->model->fix();
			$this->model->rebuild();
			$this->flashSession->success(Text::_('data-rebuilt-msg'));
		}

		return $this->redirectBack();
	}

	protected function updateNode($node)
	{
		return $this->getDI()
			->get('db')
			->execute('UPDATE ' . $this->model->getSource() . ' SET lft = :lft, rgt = :rgt, level = :level, parentId = :parentId WHERE id = :id',
				[
					'id'       => $node->id,
					'lft'      => $node->lft,
					'rgt'      => $node->rgt,
					'level'    => $node->level,
					'parentId' => $node->parentId,
				]
			);
	}

	protected function rebuildNodes($nodeItems, $nodeId = null, $lft = 0, $level = 0, $parentId = 0)
	{
		/** @var Nested $model */
		$model = $this->model;

		if (null === $nodeId)
		{
			$nodeId = $model->getRootId();
		}

		$rgt = $lft + 1;

		if (!empty($nodeItems))
		{
			foreach ($nodeItems as $nodeItem)
			{
				$rgt = $this->rebuildNodes($nodeItem['children'], $nodeItem['id'], $rgt, $level + 1, $nodeId);
			}
		}

		$node           = $model->getNode($nodeId);
		$node->lft      = $lft;
		$node->rgt      = $rgt;
		$node->level    = $level;
		$node->parentId = $parentId;

		if (false === $this->updateNode($node))
		{
			$this->getDI()->get('db')->execute('UNLOCK TABLES');
			$model->fix();
			$model->rebuild();

			return $this->response->setJsonContent('FAILURE');
		}

		return $rgt + 1;
	}

	public function updateNodesAction()
	{
		if (Form::checkToken()
			&& $this->request->isPost()
			&& $this->request->isAjax()
			&& ($this->model instanceof Nested)
			&& ($nodeItems = $this->request->getPost('nodes', null, []))
		)
		{
			/** @var Mysql $db */
			$db = $this->getDI()->get('db');

			// Lock the table
			$db->execute('LOCK TABLE ' . $this->model->getSource() . ' WRITE');

			$this->rebuildNodes($nodeItems);

			// Unlock tables
			$db->execute('UNLOCK TABLES');

			return $this->response->setJsonContent('OK');
		}
	}

	public function modifyNodeAction()
	{
		/** @var Nested $model */
		$model  = $this->model;
		$nodeId = $this->request->getPost('nodeId', ['int'], 0);
		$action = $this->request->getPost('action', ['trim', 'string'], '');

		if (Form::checkToken()
			&& $this->request->isPost()
			&& $this->request->isAjax()
			&& ($model instanceof Nested)
			&& in_array($action, ['P', 'U', 'T', 'unlock'])
		)
		{
			if ($model->modifyNode($nodeId, $action))
			{
				$this->indexAction();

				return $this->response->setJsonContent(
					[
						'success' => true,
						'message' => Text::_('action-' . strtolower($action) . '-success-msg'),
						'data'    => $this->view->getPartial('Administrator/UcmItem/Nested'),
					]
				);
			}

			return $this->response->setJsonContent(
				[
					'success' => true,
					'message' => Text::_('error-found'),
				]
			);
		}
	}

	public function indexToolBar($activeState = null, $excludes = [])
	{
		if ($this->model instanceof Nested)
		{
			$excludes = ['trash', 'copy', 'unlock'];
		}

		parent::indexToolBar($activeState, $excludes);
	}

	protected function handleSaveFields($fields, $languages, $formData)
	{
		/**
		 * @var Mysql $db
		 * @var Field $field
		 */
		$db                 = Factory::getService('db');
		$prefix             = $this->modelsManager->getModelPrefix();
		$insertFieldsValues = [];
		$insertFieldsBind   = [];
		$deleteFieldsValues = [];
		$insertTransValues  = [];
		$insertTransBind    = [];
		$walk               = 0;

		foreach ($fields as $field)
		{
			$fieldId              = $field->get('ucmFieldId');
			$value                = $field->getValue();
			$deleteFieldsValues[] = $fieldId;
			$insertValue          = is_array($value) ? json_encode($value) : $value;

			if (empty($insertValue) && '0' !== $insertValue)
			{
				continue;
			}

			$k0                    = 'fieldId' . $walk;
			$k1                    = 'itemId' . $walk;
			$k2                    = 'value' . $walk;
			$insertFieldsValues[]  = '(:' . $k0 . ',:' . $k1 . ',:' . $k2 . ')';
			$insertFieldsBind[$k0] = $fieldId;
			$insertFieldsBind[$k1] = $this->model->id;
			$insertFieldsBind[$k2] = $insertValue;

			if ($field->get('translate', false))
			{
				$rawName = $field->getName(true);

				foreach ($languages as $langCode => $language)
				{
					if (isset($formData['translations'][$langCode]['fields'][$rawName]))
					{
						$tranValue = $field->applyFilters($formData['translations'][$langCode]['fields'][$rawName]);

						if ($tranValue !== $value)
						{
							$k0                   = 'translationId' . $walk;
							$k1                   = 'originalValue' . $walk;
							$k2                   = 'translatedValue' . $walk;
							$insertTransValues[]  = '(:' . $k0 . ',:' . $k1 . ',:' . $k2 . ')';
							$insertTransBind[$k0] = $langCode . '.ucm_field_values.fieldId=' . $fieldId . ',itemId=' . $this->model->id . '.value';
							$insertTransBind[$k1] = $insertValue;
							$insertTransBind[$k2] = is_array($tranValue) ? json_encode($tranValue) : $tranValue;
						}
					}
				}
			}

			$walk++;
		}

		if ($deleteFieldsValues)
		{
			$db->execute('DELETE FROM ' . $prefix . 'ucm_field_values WHERE fieldId IN (' . implode(',', $deleteFieldsValues) . ') AND itemId = ' . $this->model->id);
		}

		if ($insertFieldsValues)
		{
			$db->execute('INSERT INTO ' . $prefix . 'ucm_field_values(fieldId,itemId,value) VALUES ' . implode(', ', $insertFieldsValues), $insertFieldsBind);
		}

		if ($insertTransValues)
		{
			$db->execute('INSERT INTO ' . $prefix . 'translations(translationId,originalValue,translatedValue) VALUES ' . implode(',', $insertTransValues), $insertTransBind);			
		}
	}

	protected function doAfterSave(array $validData)
	{
		if ($this->model instanceof Nested)
		{
			return;
		}

		/**
		 * @var Mysql       $db
		 * @var FormManager $form
		 * @var FormManager $formFields
		 */		

		$formsFields = UcmField::buildUcmFormsFields($this->model->context, $validData['parentId']);
		$formFields  = UcmField::buildUcmFormFields($this->model->context, $validData['parentId']);
		$formData    = $this->request->getPost('FormData', null, []);
		$fieldsData  = isset($formData['fields']) ? $formData['fields'] : [];
		$languages   = Language::getExistsLanguages();

		if (Language::isMultilingual() && !empty($validData['id']))
		{
			$db     = Factory::getService('db');
			$prefix = $this->modelsManager->getModelPrefix();

			// Purge translations data
			$db->execute('DELETE FROM ' . $prefix . 'translations WHERE translationId LIKE :translationId',
				[
					'translationId' => '%.ucm_field_values.fieldId=%,itemId=' . $validData['id'] . '.%',
				]
			);
		}

		if ($formsFields->count())
		{
			foreach ($formsFields->getForms() as $form)
			{
				if ($form->count())
				{
					if (!$form->isValid($fieldsData))
					{
						throw new Exception(implode('<br/>', $form->getMessages()));
					}

					$this->handleSaveFields($form->getFields(), $languages, $formData);
				}
			}
		}

		if ($formFields->count())
		{
			if (!$formFields->isValid($fieldsData))
			{
				throw new Exception(implode('<br/>', $formFields->getMessages()));
			}

			$this->handleSaveFields($formFields->getFields(), $languages, $formData);
		}
	}
}