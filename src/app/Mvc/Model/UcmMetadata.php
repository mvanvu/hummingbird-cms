<?php

namespace App\Mvc\Model;


class UcmMetadata extends ModelBase
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
	 * @var integer
	 */
	public $referenceId;

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
	 * @var string
	 */
	public $metaRobots = '';

	/**
	 *
	 * @var array
	 */
	protected $translationFields = [
		'metaTitle',
		'metaKeys',
		'metaDesc',
		'metaRobots',
	];

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('ucm_metadata');
	}
}
