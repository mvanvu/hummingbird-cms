<?php

namespace App\Mvc\Controller;

use App\Helper\User;
use App\Mvc\Model\UcmGroupField;
use MaiVu\Php\Form\FormsManager;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\Query\BuilderInterface;

class AdminUcmGroupFieldController extends AdminControllerBase
{
	/**
	 * @var UcmGroupField
	 */
	public $model = 'UcmGroupField';

	/**
	 * @var string
	 */
	public $pickedView = 'UcmGroupField';

	/**
	 * @var string
	 */
	public $context;

	/**
	 * @var string
	 */
	public $stateField = null;

	public function onConstruct()
	{
		$this->context = $this->dispatcher->getParam('context');

		if (!User::getActive()->authorise($this->context . '.manageField'))
		{
			User::forward403();
		}

		parent::onConstruct();
		$this->uri->setVar('uri', 'group-field/' . $this->context);
		$this->uri->setBaseUri('group-field/' . $this->context);
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$formsManager->get('UcmGroupField')
			->getField('context')
			->setValue($this->context);
	}

	protected function prepareIndexQuery(BuilderInterface $query)
	{
		$query->andWhere('item.context = :context:', ['context' => $this->context]);
	}
}