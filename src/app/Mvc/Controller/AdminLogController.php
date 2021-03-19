<?php

namespace App\Mvc\Controller;

use App\Mvc\Model\Log;
use App\Traits\Permission;

class AdminLogController extends AdminControllerBase
{
	/**
	 * @var Log
	 */
	public $model = 'Log';

	/**
	 * @var string
	 */
	public $pickedView = 'Log';

	/**
	 * @var string
	 */
	public $role = 'super';

	/**
	 * @var null
	 */
	public $stateField = null;

	use Permission;

	protected function indexToolBar($activeState = null, $excludes = ['add', 'copy', 'unlock', 'trash'])
	{
		parent::indexToolBar($activeState, $excludes);
	}
}
