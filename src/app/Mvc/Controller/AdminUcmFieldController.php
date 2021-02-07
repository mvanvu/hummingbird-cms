<?php

namespace App\Mvc\Controller;

use App\Helper\Uri;
use App\Helper\User;
use App\Mvc\Model\UcmField;
use MaiVu\Php\Form\FormsManager;
use Phalcon\Mvc\Model\Query\BuilderInterface;

class AdminUcmFieldController extends AdminControllerBase
{
	/**
	 * @var UcmField
	 */
	public $model = 'UcmField';

	/**
	 * @var string
	 */
	public $pickedView = 'UcmField';

	/**
	 * @var string
	 */
	public $context;

	public function onConstruct()
	{
		$this->context = $this->dispatcher->getParam('context');

		if (!User::getActive()->authorise($this->context . '.manageField'))
		{
			User::forward403();
		}

		parent::onConstruct();
	}

	protected function prepareUri(Uri $uri)
	{
		$uri->setVar('uri', 'field/' . $this->context);
		$uri->setBaseUri('field/' . $this->context);
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$generalForm = $formsManager->get('UcmField');
		$generalForm->getField('context')->setValue($this->context);
		$generalForm->getField('cid')->set('context', $this->context . '-category');

		if ($this->model->id && $this->model->categories->count())
		{
			$cid = [];

			foreach ($this->model->categories as $category)
			{
				$cid[] = $category->id;
			}

			$generalForm->getField('cid')->setValue($cid);
		}
	}

	protected function prepareIndexQuery(BuilderInterface $query)
	{
		$query->andWhere('item.context = :context:', ['context' => $this->context]);
	}
}