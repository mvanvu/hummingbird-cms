<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use Phalcon\Db\Adapter\Pdo\Mysql;

class UcmGroupField extends ModelBase
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
	protected $titleField = 'title';

	/**
	 * Initialize method for model.
	 */

	public function initialize()
	{
		$this->setSource('ucm_field_groups');
		$this->hasMany(['id', 'context'], UcmField::class, ['groupId', 'context'],
			[
				'alias'  => 'fields',
				'reuse'  => true,
				'params' => [
					'conditions' => UcmField::class . '.state = :published:',
					'bind'       => [
						'published' => 'P',
					],
				],
			]
		);
	}

	public function getOrderFields()
	{
		return [
			'createdAt',
			'title',
			'id',
		];
	}

	public function afterDelete()
	{
		/** @var Mysql $db */
		$db     = $this->getDI()->get('db');
		$prefix = $this->getModelsManager()->getModelPrefix();

		// Update fields to no group
		$db->execute('UPDATE ' . $prefix . 'ucm_fields SET groupId = 0 WHERE groupId = :groupId',
			[
				'groupId' => $this->id,
			]
		);

		// Purge translation data
		$db->execute('DELETE FROM ' . $prefix . 'translations WHERE translationId LIKE :translationId',
			[
				'translationId' => '%.ucm_field_groups.id=' . $this->id . '.%',
			]
		);
	}
}