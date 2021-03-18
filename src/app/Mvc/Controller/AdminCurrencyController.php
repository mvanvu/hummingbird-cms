<?php

namespace App\Mvc\Controller;

use App\Mvc\Model\Currency;
use App\Traits\Permission;

class AdminCurrencyController extends AdminControllerBase
{
	/**
	 * @var Currency
	 */
	public $model = 'Currency';

	/**
	 * @var string
	 */
	public $pickedView = 'Currency';

	/**
	 * @var string
	 */
	public $role = 'super';

	use Permission;


}
