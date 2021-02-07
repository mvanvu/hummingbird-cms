<?php

namespace App\Mvc\Controller;

use App\Mvc\Model\Tag;

class AdminTagController extends AdminControllerBase
{
	/**
	 * @var Tag
	 */
	public $model = 'Tag';

	/**
	 * @var string
	 */
	public $pickedView = 'Tag';

	/**
	 * @var null
	 */
	public $stateField = null;
}
