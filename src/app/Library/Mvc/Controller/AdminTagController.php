<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use MaiVu\Hummingbird\Lib\Mvc\Model\Tag;

class AdminTagController extends AdminControllerBase
{
	/** @var Tag */
	public $model = 'Tag';

	/** @var string */
	public $pickedView = 'Tag';

	/** @var null */
	public $stateField = null;
}
