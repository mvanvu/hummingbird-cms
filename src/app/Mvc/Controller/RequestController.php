<?php

namespace App\Mvc\Controller;

use App\Helper\Event;
use Throwable;

class RequestController extends ControllerBase
{
	public function getAction()
	{
		if (!$this->request->isGet())
		{
			return $this->responseError();
		}

		$callBack     = $this->getCallBack();
		$requestData  = $this->getRequestData();
		$responseData = [];

		try
		{
			Event::trigger('onRequestGet' . $callBack, [$requestData, &$responseData]);

			if ($responseData instanceof Throwable)
			{
				throw $responseData;
			}
		}
		catch (Throwable $e)
		{
			return $this->responseError($e->getCode(), $e->getMessage());
		}

		return $this->responseJson($responseData);
	}

	protected function responseError($code = 400, $message = 'Bad request')
	{
		$this->response->setStatusCode($code);

		return $this->responseJson(
			[
				'success' => false,
				'message' => $message,
			]
		);
	}

	protected function responseJson($data)
	{
		return $this->response->setJsonContent($data);
	}

	protected function getCallBack()
	{
		$callBack = trim($this->dispatcher->getParam('callback', ['trim', 'string'], ''), '-');

		if (strpos($callBack, '-') !== false)
		{
			$callBack = preg_split('/\-+/', $callBack);
			$callBack = array_map('ucfirst', $callBack);
			$callBack = join('', $callBack);
		}

		return ucfirst($callBack);
	}

	protected function getRequestData()
	{
		$requestData = [];
		$params      = $this->dispatcher->getParams();

		if (isset($params[0]))
		{
			if (is_numeric($params[0]))
			{
				$requestData['id'] = (int) $params[0];
			}
			else
			{
				parse_str(trim($params[0], '?/'), $requestData);
			}
		}

		if ($this->request->getContentType() === 'application/json' && ($jsonData = $this->request->getJsonRawBody(true)))
		{
			$requestData = array_merge($requestData, $jsonData);
		}
		elseif ($this->request->isPost())
		{
			$requestData = array_merge($requestData, $this->request->getPost(null, null, []));
		}

		return $requestData;
	}

	public function postAction()
	{
		if (!$this->request->isPost())
		{
			return $this->responseError();
		}

		$callBack     = $this->getCallBack();
		$requestData  = array_merge($this->getRequestData(), $this->request->getPost());
		$responseData = [];

		try
		{
			Event::trigger('onRequestPost' . $callBack, [$requestData, &$responseData]);

			if ($responseData instanceof Throwable)
			{
				throw $responseData;
			}
		}
		catch (Throwable $e)
		{
			return $this->responseError($e->getCode(), $e->getMessage());
		}

		return $this->responseJson($responseData);
	}
}
