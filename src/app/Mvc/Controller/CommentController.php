<?php

namespace App\Mvc\Controller;

use App\Helper\Date;
use App\Helper\User;
use App\Helper\Text;
use App\Helper\Comment;
use App\Mvc\Model\UcmComment;
use App\Mvc\Model\UcmItem;

class CommentController extends ControllerBase
{
	public function postAction()
	{
		$referenceContext = $this->dispatcher->getParam('referenceContext', ['string', 'trim']);
		$referenceId      = (int) $this->request->getPost('referenceId', ['absint'], 0);
		$parentId         = (int) $this->request->getPost('parentId', ['absint'], 0);
		$type             = $this->request->getPost('type', ['string', 'trim'], '');
		$user             = User::getActive();

		if (!$this->request->isPost()
			|| !$this->request->isAjax()
			|| !($ucmItem = UcmItem::findFirst('id = ' . $referenceId . ' AND state = \'P\''))
			|| $ucmItem->params->get('allowUserComment', 'N') !== 'Y'
			|| ($ucmItem->params->get('commentAsGuest', 'N') !== 'Y' && $user->is('guest'))
			|| !in_array($type, ['comment', 'reply'], true)
			|| $referenceId < 1
			|| ('reply' === $type && ($parentId < 1 || !UcmComment::findFirst('id = ' . $parentId . ' AND state = \'P\'')))
		)
		{
			$this->dispatcher->forward(
				[
					'controller' => 'error',
					'action'     => 'show',
				]
			);

			return false;
		}

		$commentAsGuest = $ucmItem->params->get('commentAsGuest', 'N') === 'Y';
		$autoPublish    = $ucmItem->params->get('autoPublishComment', 'N') === 'Y';
		$userComment    = $this->request->getPost('userComment', ['string', 'trim'], '');
		$response       = [
			'status'  => 'danger',
			'message' => [],
			'data'    => null,
		];

		if ($user->is('guest'))
		{
			$userName  = $this->request->getPost('userName', ['string', 'trim'], '');
			$userEmail = filter_var($this->request->getPost('userEmail', ['email'], ''), FILTER_VALIDATE_EMAIL);
		}
		else
		{
			$userName  = $user->__get('name');
			$userEmail = $user->__get('email');
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
			/** @var UcmComment $commentModel */
			$commentModel                   = new UcmComment;
			$commentModel->userName         = $userName;
			$commentModel->userEmail        = $userEmail;
			$commentModel->userComment      = $userComment;
			$commentModel->referenceContext = $referenceContext;
			$commentModel->referenceId      = $referenceId;
			$commentModel->parentId         = 'reply' === $type ? $parentId : 0;
			$commentModel->state            = $autoPublish ? 'P' : 'U';
			$commentModel->createdAt        = Date::getInstance()->toSql();
			$commentModel->createdBy        = $user->getEntity()->id;

			if ($commentModel->save())
			{
				if ($autoPublish)
				{
					$response['status']  = 'success';
					$response['message'] = [Text::_('comment-success-msg')];
				}
				else
				{
					$response['status']  = 'warning';
					$response['message'] = [Text::_('comment-warning-msg')];
				}

				$commentInstance                   = new Comment;
				$commentInstance->referenceContext = $referenceContext;
				$commentInstance->referenceId      = $referenceId;
				$commentInstance->totalItems       = 1;

				if ('reply' === $type)
				{
					$commentInstance->items = [$commentModel->getRelated('parent')];
				}
				else
				{
					$commentInstance->items = [$commentModel];
				}

				$response['data'] = $this->view->getPartial('Comment/Comment',
					[
						'commentInstance' => $commentInstance,
						'ucmItem'         => $ucmItem,
					]
				);
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
		$referenceId      = (int) $this->request->getPost('referenceId', ['absint'], 0);
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
				'data' => $this->view->getPartial('Comment/Comment',
					[
						'commentInstance' => Comment::getInstance($referenceContext, $referenceId, $offset),
						'ucmItem'         => $ucmItem,
					]
				),
			];
		}
		else
		{
			$response = [
				'data' => '',
			];
		}

		return $this->response->setJsonContent($response);
	}
}