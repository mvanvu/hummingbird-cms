<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use Phalcon\Mvc\Model;

class UcmItemMap extends Model
{
	/**
	 *
	 * @var integer
	 */
	public $itemId1;

	/**
	 *
	 * @var integer
	 */
	public $itemId2;

	/**
	 *
	 * @var string
	 */
	public $context;

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('ucm_item_map');
	}
}
