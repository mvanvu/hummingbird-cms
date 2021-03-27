<?php

namespace App\Mvc\Controller;

use App\Helper\Assets;
use App\Helper\Database;
use App\Helper\Event;
use App\Helper\Language;
use App\Helper\Nested as NestedHelper;
use App\Helper\Service;
use App\Helper\Text;
use App\Helper\UcmField;
use App\Helper\UcmItem as UcmItemHelper;
use App\Helper\Uri;
use App\Helper\User;
use App\Mvc\Model\Nested;
use App\Mvc\Model\UcmItem;
use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Form\Form as FormManager;
use MaiVu\Php\Form\FormsManager;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Mvc\Model\Query\BuilderInterface;

class AdminUcmItemController extends AdminControllerBase
{
	/**
	 * @var UcmItem
	 */
	public $model = 'UcmItem';

	/**
	 * @var string
	 */
	public $pickedView = 'UcmItem';

	/**
	 * @var string
	 */
	public $mainEditFormName = 'UcmItem';

	/**
	 * @var string
	 */
	public $context;

	/**
	 * @var string
	 */
	public $contextAlias;

	/**
	 * @var string | null
	 */
	public $permitPkgName = null;

	public function onConstruct()
	{
		$this->context       = $this->dispatcher->getParam('context');
		$this->contextAlias  = UcmItemHelper::prepareContext($this->context);
		$this->model         = $this->contextAlias;
		$this->permitPkgName = $this->context;
		parent::onConstruct();
	}

	public function rebuildAction()
	{
		if ($this->request->isPost()
			&& ($this->model instanceof Nested)
			&& User::getActive()->is('super')
		)
		{
			$this->model->fix();
			$this->model->rebuild();
			$this->flashSession->success(Text::_('data-rebuilt-msg'));
		}

		return $this->redirectBack();
	}

	public function updateNodesAction()
	{
		if ($this->request->isPost()
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

	public function modifyNodeAction()
	{
		/** @var Nested $model */
		$model  = $this->model;
		$nodeId = $this->request->getPost('nodeId', ['int'], 0);
		$action = $this->request->getPost('action', ['trim', 'string'], '');

		if ($this->request->isPost()
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
						'data'    => $this->view->getPartial('UcmItem/Nested'),
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

	public function indexAction()
	{
		parent::indexAction();
		$this->handleNested();
	}

	protected function handleNested()
	{
		if ($this->model instanceof Nested)
		{
			Text::script('please-wait-msg');
			Text::script('data-rebuilt-msg');
			Text::script('modify-node-confirm');
			Text::script('confirm-rebuild-nested-msg');
			Assets::jQueryCore();
			Assets::add(
				[
					'css/jquery.nestable.css',
					'js/jquery.nestable.js',
					'js/nested.js',
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

	public function indexToolBar($activeState = null, $excludes = [])
	{
		if ($this->model instanceof Nested)
		{
			$excludes = ['trash', 'copy', 'unlock'];
		}

		parent::indexToolBar($activeState, $excludes);
	}

	protected function prepareIndexQuery(BuilderInterface $query)
	{
		$results = Event::trigger('beforeUcm' . $this->contextAlias . 'PrepareIndexQuery', [$this, $query], ['Cms']);

		if (!in_array(false, $results, true))
		{
			$query->andWhere('item.context = :context:', ['context' => $this->context]);
		}
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$formsManager->get('UcmItem')->getField('context')->setValue($this->context);
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

	protected function doAfterSave($validData, $isNew)
	{
		/**
		 * @var Mysql       $db
		 * @var FormManager $form
		 * @var FormManager $formFields
		 */

		$formsFields = UcmField::buildUcmFormsFields($this->model->context, $validData['parentId']);
		$formFields  = UcmField::buildUcmFormFields($this->model->context, $validData['parentId']);

		if (Language::isMultilingual() && !$isNew)
		{
			$db = Service::db();

			// Purge translations data
			$db->execute('DELETE FROM ' . Database::table('translations') . ' WHERE translationId LIKE :translationId',
				[
					'translationId' => '%.ucm_field_values.fieldId=%,itemId=' . $validData['id'],
				]
			);
		}

		if ($formsFields->count() && $formsFields->isValidRequest())
		{
			foreach ($formsFields->getForms() as $form)
			{
				$this->handleSaveFields($form);
			}
		}

		if ($formFields->count() && $formFields->isValidRequest())
		{
			$this->handleSaveFields($formFields);
		}
	}

	protected function handleSaveFields(Form $form)
	{
		/**
		 * @var Mysql $db
		 * @var Field $field
		 */
		$db                 = Service::db();
		$prefix             = $this->modelsManager->getModelPrefix();
		$insertFieldsValues = [];
		$insertFieldsBind   = [];
		$deleteFieldsValues = [];
		$insertTransValues  = [];
		$insertTransBind    = [];
		$walk               = 0;

		foreach ($form->getFields() as $field)
		{
			$fieldId               = $field->get('dataAttributes')['field-id'];
			$value                 = $field->getValue();
			$deleteFieldsValues[]  = $fieldId;
			$insertValue           = is_array($value) ? json_encode($value) : (string) $value;
			$k0                    = 'fieldId' . $walk;
			$k1                    = 'itemId' . $walk;
			$k2                    = 'value' . $walk;
			$insertFieldsValues[]  = '(:' . $k0 . ',:' . $k1 . ',:' . $k2 . ')';
			$insertFieldsBind[$k0] = $fieldId;
			$insertFieldsBind[$k1] = $this->model->id;
			$insertFieldsBind[$k2] = $insertValue;

			if ($field->get('translate', false))
			{
				foreach ($field->getTranslateFields() as $translateField)
				{
					$language  = $translateField->get('language');
					$tranValue = $translateField->getValue();

					if (null !== $tranValue && $tranValue !== $value)
					{
						$k0                   = 'translationId' . $walk;
						$k1                   = 'translatedValue' . $walk;
						$insertTransValues[]  = '(:' . $k0 . ',:' . $k1 . ')';
						$insertTransBind[$k0] = $language . '.ucm_field_values.fieldId=' . $fieldId . ',itemId=' . $this->model->id;
						$insertTransBind[$k1] = json_encode([$tranValue]);
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
			$db->execute('INSERT INTO ' . $prefix . 'translations(translationId,translatedValue) VALUES ' . implode(',', $insertTransValues), $insertTransBind);
		}
	}
}