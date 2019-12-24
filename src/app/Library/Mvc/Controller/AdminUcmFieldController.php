<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Mvc\Model\Query\BuilderInterface;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Form\FormsManager;
use MaiVu\Hummingbird\Lib\Mvc\Model\UcmField;

class AdminUcmFieldController extends AdminControllerBase
{
	/** @var UcmField */
	public $model = 'UcmField';

	/** @var string */
	public $pickedView = 'UcmField';

	/** @var string */
	public $context;

	public function onConstruct()
	{
		$this->context = $this->dispatcher->getParam('context');
		parent::onConstruct();
	}

	protected function prepareUri(Uri $uri)
	{
		$uri->setVar('uri', 'field/' . $this->context);
		$uri->setBaseUri('field/' . $this->context);
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$generalForm = $formsManager->get('general');
		$generalForm->getField('context')->setValue($this->context);
		$generalForm->getField('cid')->set('context', $this->context . '-category');

		if ($this->model->id
			&& $this->model->categories->count()
		)
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