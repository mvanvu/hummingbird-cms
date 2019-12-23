<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Mvc\Controller;

class AdminRawController extends Controller
{
	public function onConstruct()
	{
		$forward = [
			'controller' => $this->dispatcher->getParam('forward'),
			'action'     => $this->dispatcher->getActionName(),
		];

		if (empty($forward['action']))
		{
			$forward['action'] = 'index';
		}

		if ($id = $this->dispatcher->getParam('id'))
		{
			$forward['id'] = (int) $id;
		}

		$this->dispatcher->setParam('format', 'raw');

		return $this->dispatcher->forward($forward);
	}
}
