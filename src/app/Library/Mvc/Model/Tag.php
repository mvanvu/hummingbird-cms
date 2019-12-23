<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use MaiVu\Php\Filter;

class Tag extends ModelBase
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
	public $title;

	/**
	 *
	 * @var string
	 */
	public $slug;

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
	 * @var string
	 */

	protected $titleField = 'title';

	/**
	 * @var boolean
	 */
	protected $standardMetadata = true;

	public function initialize()
	{
		$this->setSource('tags');
	}

	public function getOrderFields()
	{
		return [
			'title',
			'createdAt',
			'id',
		];
	}

	public function getSearchFields()
	{
		return [
			'title',
			'slug',
		];
	}

	public function beforeValidation()
	{
		if (empty($this->slug))
		{
			$this->slug = Filter::toSlug($this->title);
		}
	}
}
