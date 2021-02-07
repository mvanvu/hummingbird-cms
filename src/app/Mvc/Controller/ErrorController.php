<?php

namespace App\Mvc\Controller;

use App\Helper\State;
use App\Helper\Text;
use Throwable;

class ErrorController extends ControllerBase
{
	public function showAction()
	{
		// Default error code is 404
		$vars = [
			'code'      => $this->dispatcher->getParam('code', ['int'], 404),
			'title'     => $this->dispatcher->getParam('title', ['trim', 'string'], Text::_('404-title')),
			'message'   => $this->dispatcher->getParam('message', ['trim', 'string'], Text::_('404-message')),
			'exception' => State::getMark('exception'),
		];

		if ($vars['exception'] instanceof Throwable)
		{
			$vars['code'] = $vars['exception']->getCode();
		}

		$this->view->pick('Error/Message');
		$this->view->setVars($vars);
	}
}
