<?php

namespace App\Mvc\Controller;

use App\Helper\Date;
use App\Helper\Text;
use App\Helper\User;
use App\Mvc\Model\UcmComment;
use App\Mvc\Model\UcmItem;

class CommentController extends ControllerBase
{
	public function postAction()
	{
		$referenceContext = $this->dispatcher->getParam('referenceContext', ['string', 'trim']);
		$referenceId      = (int) $this->request->getPost('referenceId', ['absint'], 0);
		$parentId         = (int) $this->request->getPost('parentId', ['absint'], 0);

		if (!$this->request->isPost()
			|| !$this->request->isAjax()
			|| !($ucmItem = UcmItem::findFirst('id = ' . $referenceId . ' AND state = \'P\''))
			|| ($parentId > 0 && !UcmComment::findFirst('id = ' . $parentId . ' AND state = \'P\''))
			|| $ucmItem->params->get('allowUserComment', 'N') !== 'Y'
			|| ($ucmItem->params->get('commentAsGuest', 'N') !== 'Y' && User::is('guest'))
		)
		{
			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => Text::_('403-message'),
				]
			);
		}

		$commentAsGuest = $ucmItem->params->get('commentAsGuest', 'N') === 'Y';
		$autoPublish    = $ucmItem->params->get('autoPublishComment', 'N') === 'Y';
		$userComment    = $this->request->getPost('userComment', ['string', 'trim'], '');
		$response       = [
			'success' => false,
			'message' => [],
		];

		if (User::is('guest'))
		{
			$userName  = $this->request->getPost('userName', ['string', 'trim'], '');
			$userEmail = filter_var($this->request->getPost('userEmail', ['email'], ''), FILTER_VALIDATE_EMAIL);
		}
		else
		{
			$userName  = User::name();
			$userEmail = User::email();
		}

		if ($commentAsGuest)
		{
			if (empty($userName))
			{
				$response['message'][] = Text::_('comment-invalid-name-msg');
			}

			if (empty($userEmail))
			{
				$response['message'][] = Text::_('comment-invalid-email-msg');
			}
		}

		if (empty($userComment))
		{
			$response['message'][] = Text::_('comment-invalid-content-msg');
		}

		if (empty($response['message']))
		{
			$commentModel                   = new UcmComment;
			$commentModel->userName         = $userName;
			$commentModel->userEmail        = $userEmail;
			$commentModel->userComment      = $userComment;
			$commentModel->referenceContext = $referenceContext;
			$commentModel->referenceId      = $referenceId;
			$commentModel->parentId         = $parentId;
			$commentModel->state            = $autoPublish ? 'P' : 'U';
			$commentModel->createdAt        = Date::now('UTC')->toSql();
			$commentModel->createdBy        = User::id();

			if ($commentModel->save())
			{
				if ($autoPublish)
				{
					$response['success'] = true;
					$response['message'] = [Text::_('comment-success-msg')];
				}
				else
				{
					$response['message'] = [Text::_('comment-warning-msg')];
				}

				$response['data'] = $this->view->getPartial('Comment/Comment', ['ucmItem' => $ucmItem]);
			}
			else
			{
				foreach ($commentModel->getMessages() as $message)
				{
					$response['message'][] = (string) $message;
				}
			}
		}

		$response['message'] = implode('<br/>', $response['message']);

		return $this->response->setJsonContent($response);
	}

	public function viewMoreAction()
	{
		$referenceContext = $this->dispatcher->getParam('referenceContext');
		$referenceId      = (int) $this->dispatcher->getParam('referenceId', ['absint'], 0);
		$offset           = (int) $this->dispatcher->getParam('offset', ['absint'], 0);
		$ucmItem          = UcmItem::findFirst(
			[
				'conditions' => 'id = :id: AND context = :context: AND state = :publish:',
				'bind'       => [
					'context' => $referenceContext,
					'id'      => $referenceId,
					'publish' => 'P',
				],
			]
		);

		if ($ucmItem)
		{
			$response = [
				'success' => true,
				'data'    => $this->view->getPartial('Comment/Comment',
					[
						'ucmItem' => $ucmItem,
						'offset'  => $offset,
					]
				),
			];
		}
		else
		{
			$response = [
				'success' => false,
			];
		}

		return $this->response->setJsonContent($response);
	}
}