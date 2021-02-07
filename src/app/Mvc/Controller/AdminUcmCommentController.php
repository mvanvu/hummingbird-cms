<?php

namespace App\Mvc\Controller;

use App\Helper\Event as EventHelper;
use App\Helper\Text;
use App\Helper\Uri;
use App\Mvc\Model\UcmComment;
use MaiVu\Php\Form\FormsManager;
use Phalcon\Mvc\Model\Query\BuilderInterface;

class AdminUcmCommentController extends AdminControllerBase
{
	/**
	 * @var UcmComment
	 */
	public $model = 'UcmComment';

	/**
	 * @var string
	 */
	public $pickedView = 'UcmComment';

	public function onConstruct()
	{
		parent::onConstruct();
		EventHelper::trigger('beforeUcmComment' . ucfirst($this->dispatcher->getActionName()), [$this], ['Cms']);
		$this->model->referenceContext = $this->dispatcher->getParam('referenceContext');
		$this->model->permitPkgName    = $this->model->referenceContext;
	}

	public function indexToolBar($activeState = null, $excludes = ['add', 'copy'])
	{
		parent::indexToolBar($activeState, $excludes);
	}

	protected function prepareIndexQuery(BuilderInterface $query)
	{
		$query->andWhere('item.referenceContext = :context:', ['context' => $this->model->referenceContext]);
	}

	protected function prepareUri(Uri $uri)
	{
		$baseUri = $this->dispatcher->getParam('referenceContext') . '/comment';
		$uri->setVar('uri', $baseUri);
		$uri->setBaseUri($baseUri);
	}

	protected function indexTitle()
	{
		$this->tag->setTitle(Text::_($this->model->referenceContext . '-comment-admin-index-title'));
	}

	protected function editTitle()
	{
		if ($this->model->id)
		{
			$this->tag->setTitle(Text::_($this->model->referenceContext . '-comment-admin-edit-title', ['userName' => $this->model->userName]));
		}
		else
		{
			$this->tag->setTitle(Text::_($this->model->referenceContext . '-comment-admin-add-title'));
		}
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$generalForm = $formsManager->get('UcmComment');
		$generalForm->getField('referenceContext')->setValue($this->model->referenceContext);
		$generalForm->getField('userIp')->setValue($this->request->getClientAddress());
		$generalForm->getField('referenceId')
			->set('context', $this->model->referenceContext)
			->set('requireMessage', $this->model->referenceContext . '-reference-required');
		EventHelper::trigger('prepareUcmCommentFormsManager', [$formsManager], ['Cms']);
	}
}
