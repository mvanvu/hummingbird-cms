<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use Phalcon\Db\Adapter\Pdo\Mysql;
use MaiVu\Hummingbird\Lib\Form\Form;
use MaiVu\Hummingbird\Lib\Factory;
use MaiVu\Php\Registry;

class UcmField extends ModelBase
{
	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var string
	 */
	public $state;

	/**
	 *
	 * @var integer
	 */
	public $groupId = 0;

	/**
	 *
	 * @var string
	 */
	public $context;

	/**
	 *
	 * @var string
	 */
	public $label;

	/**
	 *
	 * @var string
	 */
	public $type;


	/**
	 *
	 * @var string
	 */
	public $name;

	/**
	 *
	 * @var string
	 */
	public $params = '{}';

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
	 * @var boolean
	 */
	protected $standardMetadata = true;

	/**
	 *
	 * @var string
	 */
	protected $titleField = 'label';

	/**
	 * Initialize method for model.
	 */

	public function initialize()
	{
		$this->setSource('ucm_fields');
		$this->belongsTo(['groupId', 'context'], UcmGroupField::class, ['id', 'context'],
			[
				'alias' => 'group',
				'reuse' => true,
			]
		);
		$this->hasManyToMany('id', UcmItemMap::class, 'itemId1', 'itemId2', UcmItem::class, 'id',
			[
				'alias'    => 'categories',
				'reusable' => true,
				'params'   => [
					'conditions' => UcmItemMap::class . '.context = :context:',
					'bind'       => [
						'context' => 'field-category',
					],
				],
			]
		);
	}

	public function getOrderFields()
	{
		return [
			'createdAt',
			'label',
			'type',
			'id',
		];
	}

	public function getParamsFormsManager()
	{
		$paramsFormsManager = parent::getParamsFormsManager();

		// Set params form
		$paramsFormsManager->set('params', new Form('FormData.params', __DIR__ . '/Form/UcmField/Param.php'));

		return $paramsFormsManager;
	}

	public function controllerBeforeBindData(&$rawData)
	{
		if (empty($rawData['name']) && isset($rawData['label']))
		{
			$rawData['name'] = preg_replace('/[^a-zA-Z0-9_]/', '_', $rawData['label']);
		}
	}

	public function controllerDoAfterSave($validData)
	{
		/** @var Mysql $db */
		$db     = Factory::getService('db');
		$prefix = $this->getModelsManager()->getModelPrefix();
		$db->execute('DELETE FROM ' . $prefix . 'ucm_item_map WHERE context = :context AND itemId1 = :id',
			[
				'context' => 'field-category',
				'id'      => $this->id,
			]
		);

		if (!empty($validData['cid']))
		{
			$values = [];
			$id     = (int) $this->id;

			foreach (array_unique($validData['cid']) as $cid)
			{
				$cid = (int) $cid;

				if ($cid > 0)
				{
					$values[] = '(:context, ' . $id . ', ' . $cid . ')';
				}
			}

			if ($values)
			{
				$db->execute('INSERT INTO ' . $prefix . 'ucm_item_map(context, itemId1, itemId2) VALUES ' . implode(',', $values),
					[
						'context' => 'field-category',
					]
				);

			}
		}
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
		/** @var Mysql $db */
		$db     = $this->getDI()->get('db');
		$prefix = $this->getModelsManager()->getModelPrefix();

		// Purge field values
		$db->execute('DELETE FROM ' . $prefix . 'ucm_field_values WHERE fieldId = :fieldId',
			[
				'fieldId' => $this->id,
			]
		);

		// Purge translations data
		$db->execute('DELETE FROM ' . $prefix . 'translations WHERE translationId LIKE :translationId',
			[
				'translationId' => '%.ucm_fields.id=' . $this->id . '.%',
			]
		);

		$db->execute('DELETE FROM ' . $prefix . 'translations WHERE translationId LIKE :translationId',
			[
				'translationId' => '%.ucm_field_values.fieldId=' . $this->id . ',itemId=%',
			]
		);
	}
}