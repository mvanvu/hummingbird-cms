<?php

namespace App\Mvc\Model;

use Phalcon\Mvc\Model;

class UcmFieldValue extends Model
{
	/**
	 *
	 * @var integer
	 */
	public $fieldId;

	/**
	 *
	 * @var integer
	 */
	public $itemId;

	/**
	 *
	 * @var string
	 */
	public $value;

	/**
	 * Initialize method for model.
	 */

	public function initialize()
	{
		$this->setSource('ucm_field_values');
	}
}