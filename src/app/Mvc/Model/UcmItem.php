<?php

namespace App\Mvc\Model;

use App\Helper\Database;
use App\Helper\Event as EventHelper;
use App\Helper\Service;
use App\Helper\StringHelper;
use App\Helper\UcmItem as UcmItemHelper;
use App\Helper\Uri;
use MaiVu\Php\Filter;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Form\FormsManager;
use MaiVu\Php\Registry;

class UcmItem extends ModelBase
{
	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var integer
	 */
	public $parentId;

	/**
	 *
	 * @var string
	 */
	public $context;

	/**
	 *
	 * @var string
	 */
	public $title;

	/**
	 *
	 * @var string
	 */
	public $route;

	/**
	 *
	 * @var integer
	 */
	public $state;


	/**
	 *
	 * @var string
	 */
	public $image;

	/**
	 *
	 * @var string
	 */
	public $summary = null;

	/**
	 *
	 * @var string
	 */
	public $content = null;

	/**
	 *
	 * @var string
	 */
	public $description;

	/**
	 *
	 * @var string
	 */
	public $createdAt;

	/**
	 *
	 * @var integer
	 */
	public $createdBy = 0;

	/**
	 *
	 * @var string
	 */
	public $modifiedAt = null;

	/**
	 *
	 * @var integer
	 */
	public $modifiedBy = 0;

	/**
	 *
	 * @var string
	 */
	public $checkedAt = null;

	/**
	 *
	 * @var integer
	 */
	public $checkedBy = 0;

	/**
	 *
	 * @var string
	 */
	public $metaTitle = '';

	/**
	 *
	 * @var string
	 */
	public $metaKeys = '';

	/**
	 *
	 * @var string
	 */
	public $metaDesc = '';
	/**
	 *
	 * @var integer
	 */
	public $ordering = 0;
	/**
	 *
	 * @var integer
	 */
	public $lft = 0;
	/**
	 *
	 * @var integer
	 */
	public $rgt = 1;
	/**
	 *
	 * @var integer
	 */
	public $level = 0;
	/**
	 * @var integer
	 */

	public $hits = 0;
	/**
	 * @var boolean
	 */

	public $hasRoute = false;
	/**
	 *
	 * @var string
	 */
	protected $params;
	/**
	 * @var string
	 */

	protected $titleField = 'title';

	/**
	 * @var boolean
	 */

	protected $standardMetadata = true;

	/**
	 * @var array
	 */
	protected $jsonFields = ['image', 'params'];

	public function getParent()
	{
		return $this->getRelated('parent');
	}

	/**
	 * Initialize method for model.
	 */

	public function initialize()
	{
		$this->setSource('ucm_items');

		if (empty($this->permitPkgName))
		{
			$this->permitPkgName = $this->context;
		}
	}

	public function getOrderFields()
	{
		return [
			($this instanceof Nested ? 'lft' : 'ordering'),
			'id',
			'state',
			'parentId',
			'title',
			'description',
			'createdAt',
			'createdBy',
		];
	}

	public function getFilterForm()
	{
		$filterForm = Form::create('filters', MVC_PATH . '/Model/Form/UcmItem/Index/filters.php');
		$filterForm->getField('parentId')->set('context', $this->context . '-category');

		return $filterForm;
	}

	public function getFormsManager()
	{
		$formsManager = new FormsManager(
			[
				'UcmItem'  => Form::create('UcmItem', MVC_PATH . '/Model/Form/UcmItem/Edit/UcmItem.php'),
				'aside'    => Form::create('UcmItem', MVC_PATH . '/Model/Form/UcmItem/Edit/aside.php'),
				'metadata' => Form::create('UcmItem', MVC_PATH . '/Model/Form/Metadata/metadata.php'),
			]
		);

		$asideForm = $formsManager->get('aside');
		$parent    = $asideForm->getField('parentId');

		if ($this->isContextSuffix('category'))
		{
			$formsManager->set('params', Form::create('UcmItem.params', MVC_PATH . '/Model/Form/UcmItem/Edit/category-params.php'));
			$asideForm->remove('tags');
			$parent->set('context', $this->context);
		}
		else
		{
			$formsManager->set('params', Form::create('UcmItem.params', MVC_PATH . '/Model/Form/UcmItem/Edit/item-params.php'));
			$parent->set('context', $this->context . '-category');
		}

		return $formsManager;
	}

	public function isContextSuffix($suffix, $context = null)
	{
		return preg_match('/-' . $suffix . '$/', $context ?? $this->context) ? true : false;
	}

	public function isContextPrefix($prefix, $context = null)
	{
		return preg_match('/^' . $prefix . '/', $context ?? $this->context) ? true : false;
	}

	public function prepareFormsManager(FormsManager $formsManager)
	{
		if ($this->id)
		{
			$asideForm = $formsManager->get('aside');

			if ($asideForm->has('tags') && ($tags = $this->getRelated('tags'))->count())
			{
				$tagIds = [];

				foreach ($tags as $tag)
				{
					$tagIds[] = (int) $tag->id;
				}

				$asideForm->getField('tags')->setValue($tagIds);
			}
		}

		if ($formsManager->has('params') && $this->isContextSuffix('category'))
		{
			$field = $formsManager->get('params')->getField('listLimit');

			if ($field && (int) $field->getValue() === 0)
			{
				$field->setValue('');
			}
		}
	}

	public function controllerDoBeforeSave(&$validData, $isNew)
	{
		if ($this->id == $validData['parentId'])
		{
			$validData['parentId'] = 0;
		}

		if ($this->hasRoute && empty($validData['route']))
		{
			if ($validData['parentId'] && ($parent = static::findFirst($validData['parentId'])))
			{
				$validData['route'] = Filter::toPath($parent->route . '/' . $validData['title']);
			}
			else
			{
				$prefix             = $this->isContextSuffix('category') ? '' : $this->context . '/';
				$validData['route'] = $prefix . Filter::toSlug($validData['title']);
			}
		}

		$ordering = (int) abs($validData['ordering'] ?? 0);

		if ($ordering < 1)
		{
			$validData['ordering'] = parent::maximum(
					[
						'parentId = :parentId:',
						'column' => 'ordering',
						'bind'   => [
							'parentId' => $validData['parentId'] ?? 0,
						],
					]
				) + 1;
		}
	}

	public function controllerDoAfterSave($validData, $isNew)
	{
		$db = Service::db();

		$db->execute('DELETE FROM ' . Database::table('ucm_item_map') . ' WHERE itemId1 = :itemId1 AND context = :context',
			[
				'itemId1' => $this->id,
				'context' => 'tag',
			]
		);

		if (!empty($validData['tags']))
		{
			foreach (Filter::clean($validData['tags'], 'unique') as $tagId)
			{
				$tagId = (int) $tagId;

				if ($tagId > 0)
				{
					(new UcmItemMap)
						->assign(
							[
								'itemId1' => $this->id,
								'itemId2' => $tagId,
								'context' => 'tag',
							]
						)->save();
				}
			}
		}

		$context = UcmItemHelper::prepareContext($this->context);
		EventHelper::trigger('afterSaveUcm' . $context, [$this, $validData], ['Cms']);
	}

	public function delete(): bool
	{
		if ($result = parent::delete())
		{
			$context      = UcmItemHelper::prepareContext($this->context);
			$previousData = $this->toArray();
			EventHelper::trigger('afterDeleteUcm' . $context, [$previousData], ['Cms']);
		}

		return $result;
	}

	public function hits($pk = null)
	{
		if (null === $pk)
		{
			$pk = $this->id;
		}

		$session = Service::session();
		$hitsKey = $this->context . '.hits';
		$hits    = $session->get($hitsKey, []);

		if (!isset($hits[$pk]))
		{
			$hits[$pk] = 1;
			$session->set($hitsKey, $hits);
			Service::db()->execute('UPDATE ' . $this->getSource() . ' SET hits = hits + 1 WHERE id = :id',
				[
					'id' => $pk,
				]
			);

			if ($pk == $this->id)
			{
				$this->hits++;
			}
		}

		return $this;
	}

	public function getLink()
	{
		return Uri::route($this->t('route'), false, true);
	}

	public function t($field)
	{
		if (Uri::isClient('administrator'))
		{
			return $this->{$field};
		}

		static $translated = [];
		$k = $this->id . $field;

		if (!isset($translated[$k]))
		{
			$translationData = parent::getTranslations();

			if (isset($translationData[$field]))
			{
				$value = $translationData[$field];

				switch ($field)
				{
					case 'route':

						if (empty($value))
						{
							$value = $this->route;
						}

						break;

					case 'image':

						if (empty($value) || in_array($value, ['[]', '{}']))
						{
							$value = $this->image;
						}

						break;

					case 'params':
						$tranValue = $value;
						$value     = new Registry($this->{$field});
						$value->merge($tranValue);
						break;
				}
			}
			else
			{
				$value = $this->{$field};
			}

			$translated[$k] = $value;
		}

		return $translated[$k];
	}

	public function summary($fallbackDescStrLen = 100)
	{
		$this->parseSummaryContent();

		return StringHelper::truncate($this->summary ?: $this->content, $fallbackDescStrLen);
	}

	public function parseSummaryContent()
	{
		if (null === $this->summary || null === $this->content)
		{
			$this->summary = '';
			$this->content = trim($this->t('description'));
			$regex         = '/<hr\s+id=("|\')read-more("|\')[^\>]+>/';

			if (preg_match($regex, $this->content))
			{
				list($this->summary, $this->content) = preg_split($regex, $this->content, 2);
			}
		}

		return $this;
	}

	public function content()
	{
		$this->parseSummaryContent();

		return $this->content;
	}

	public function copy()
	{
		if ($result = parent::copy())
		{
			$fieldsValues = UcmFieldValue::find(
				[
					'conditions' => 'itemId = :itemId:',
					'bind'       => [
						'itemId' => $this->id,
					],
				]
			);

			if ($fieldsValues->count())
			{
				$values = '';
				$bind   = [];

				foreach ($fieldsValues as $fieldsValue)
				{
					$values .= '(?, ?, ?),';
					$bind[] = $fieldsValue->fieldId;
					$bind[] = $result->id;
					$bind[] = $fieldsValue->value;
				}

				$source = $this->getModelsManager()->getModelPrefix() . 'ucm_field_values';
				$this->getDI()->get('db')->execute('INSERT INTO ' . $source . ' (fieldId, itemId, value) VALUES ' . rtrim($values, ','), $bind);
			}
		}

		return $result;
	}

	public function getParams()
	{
		if (!($this->params instanceof Registry))
		{
			$this->params = new Registry($this->params);
		}

		return $this->params;
	}

	public function afterDelete()
	{
		$prefix = $this->getModelsManager()->getModelPrefix();
		$db     = Service::db();
		$id     = (int) $this->id;
		$db->execute('DELETE FROM ' . $prefix . 'translations WHERE translationId LIKE :translationId',
			[
				'translationId' => '%.ucm_items.id=' . $id,
			]
		);
		$db->execute('DELETE FROM ' . $prefix . 'ucm_item_map WHERE itemId1 = ' . $id);
		$db->execute('DELETE FROM ' . $prefix . 'ucm_field_values WHERE itemId LIKE :itemId',
			[
				'itemId' => $id,
			]
		);
	}
}