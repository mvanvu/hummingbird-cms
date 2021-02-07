<?php

namespace App\Factory;

use App\Helper\Event;
use App\Helper\Json;
use Exception;
use Phalcon\Mvc\Micro;
use Throwable;

class ApiApplication extends Micro
{
	public function execute()
	{
		try
		{
			Event::trigger('onBootApi', [$this], ['Api']);

			$this->before(function () {
				Event::trigger('onBeforeHandleApi', [$this], ['Api']);
			});

			$this->after(function () {
				Event::trigger('onAfterHandleApi', [$this], ['Api']);
			});

			$this->finish(function () {
				Event::trigger('onFinishHandleApi', [$this], ['Api']);
			});

			$this->handle($_SERVER['REQUEST_URI']);
		}
		catch (Throwable $e)
		{
			$this->throw($e->getMessage(), $e->getCode());
		}
	}

	public function throw($message, $code = 404)
	{
		return $this->json(new Exception($message, $code));
	}

	public function json($data, $success = true, $message = null)
	{
		return Json::getInstance($this->response)
			->response($data, $success, $message);
	}
}