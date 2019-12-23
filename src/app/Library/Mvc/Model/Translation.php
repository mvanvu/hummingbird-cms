<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use Phalcon\Mvc\Model;

class Translation extends Model
{
	/**
	 *
	 * @var string
	 */
	public $translationId;

	/**
	 *
	 * @var string
	 */
	public $originalValue;

	/**
	 * @var string
	 */

	public $translatedValue;

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('translations');
	}
}
