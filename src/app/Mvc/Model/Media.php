<?php

namespace App\Mvc\Model;

class Media extends ModelBase
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
	public $file;

	/**
	 *
	 * @var string
	 */
	public $type;

	/**
	 *
	 * @var string
	 */
	public $createdAt;

	/**
	 * @var integer
	 */

	public $createdBy;

	/**
	 * @var string
	 */

	public $mime;

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('media');
	}
}
