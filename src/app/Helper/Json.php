<?php

namespace App\Helper;

use Phalcon\Http\ResponseInterface;
use Throwable;

class Json
{
	protected $response;

	public function __construct(ResponseInterface $response = null)
	{
		if (null === $response)
		{
			$response = Service::response();
		}

		$this->response = $response;
	}

	public static function getInstance(ResponseInterface $response = null)
	{
		return new Json($response);
	}

	public function response($data, $success = true, $message = null)
	{
		$jsonData = $this->responseData($data, $success, $message);

		if ($this->response->isSent())
		{
			echo json_encode($jsonData);

			return $this->response;
		}

		return $this->response->setJsonContent($jsonData)
			->send();
	}

	public function responseData($data, $success = true, $message = null)
	{
		if ($data instanceof Throwable)
		{
			$jsonData = [
				'success' => false,
				'message' => $data->getMessage(),
				'code'    => $data->getCode(),
			];
		}
		else
		{
			$jsonData = [
				'success' => $success,
				'message' => $message,
				'data'    => $data,
			];
		}

		if ($extraData = State::getMark('jsonExtraData', []))
		{
			$jsonData = array_merge($jsonData, (array) $extraData);
		}

		return $jsonData;
	}
}