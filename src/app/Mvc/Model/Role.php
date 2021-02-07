<?php

namespace App\Mvc\Model;

use App\Helper\Database;
use App\Helper\Service;

class Role extends ModelBase
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
	public $name;

	/**
	 *
	 * @var string
	 */
	public $type;

	/**
	 *
	 * @var string
	 */
	public $description;

	/**
	 *
	 * @var string
	 */
	public $protected = 'N';

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
	public $permissions = '{}';

	/**
	 *
	 * @var string
	 */
	protected $titleField = 'name';

	/**
	 * @var boolean
	 */

	protected $standardMetadata = true;

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('roles');
		$this->skipAttributes(['protected']);
	}

	public function getOrderFields()
	{
		return [
			'id',
			'name',
			'description',
			'createdAt',
		];
	}

	public function getSearchFields()
	{
		return [
			'name',
			'description',
		];
	}

	public function delete(): bool
	{
		if ($this->isProtected())
		{
			Service::flashSession()->error('Access denied.');

			return false;
		}

		return parent::delete();
	}

	public function isProtected()
	{
		return $this->protected === 'Y';
	}

	public function afterDelete()
	{
		Service::db()->update(
			Database::table('users'),
			['roleId'],
			[Role::findFirst('protected = \'Y\' AND type = \'R\'')->id],
			[
				'conditions' => 'roleId = ?',
				'bind'       => [$this->id],
			]
		);
	}
}