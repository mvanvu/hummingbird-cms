<?php

namespace App\Mvc\Controller;

use App\Helper\Utility;
use App\Mvc\Model\Language;
use App\Traits\Permission;
use MaiVu\Php\Form\FormsManager;

class AdminLanguageController extends AdminControllerBase
{
	/**
	 * @var Language
	 */
	public $model = 'Language';

	/**
	 * @var string
	 */
	public $pickedView = 'Language';

	/**
	 * @var string
	 */
	public $role = 'super';

	use Permission;

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		if ($this->model->id && $this->model->yes('protected'))
		{
			$langForm = $formsManager->get('Language');
			$langForm->getField('state')->set('readonly', true);
			$langForm->getField('code')->set('readonly', true);
			$langForm->getField('iso')->set('readonly', true);
			$langForm->getField('sef')->set('readonly', true);
		}
	}

	protected function indexToolBar($activeState = null, $excludes = ['copy'])
	{
		parent::indexToolBar($activeState, $excludes);
		$this->view->setVar('isoCodes', array_flip(Utility::getIsoCodes()));
	}
}
