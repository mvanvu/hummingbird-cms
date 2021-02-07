<?php

namespace App\Mvc\Model;

use MaiVu\Php\Registry;

class Plugin extends ModelBase
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
	public $version;

	/**
	 *
	 * @var string
	 */
	public $group;

	/**
	 *
	 * @var string
	 */
	public $active = 'N';

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
	 * @var array|string|Registry
	 */
	public $params = '{}';

	/**
	 * @var array|string|Registry
	 */
	public $manifest = '{}';

	/**
	 *
	 * @var integer
	 */
	public $ordering = 0;

	/**
	 *
	 * @var string
	 */
	protected $titleField = 'name';

	/**
	 * @var string[]
	 */
	protected $jsonFields = ['params', 'manifest'];

	/**
	 * @var bool
	 */

	protected $standardMetadata = true;

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('plugins');
		$this->skipAttributes(['protected']);
	}

	public function getSearchFields()
	{
		return [
			'name',
			'group',
		];
	}

	public function getOrderFields()
	{
		return [
			'createdAt',
			'name',
			'group',
			'active',
			'ordering',
		];
	}
}
