<?php

namespace App\Helper;

use App\Factory\Factory;
use Phalcon\Session\Manager as SessionAdapter;

class Form
{
	public static function tokenInput()
	{
		return '<input type="hidden" name="' . static::getToken() . '" value="1"/>';
	}

	public static function getToken($remove = false)
	{
		/** @var SessionAdapter $session */
		$session = Factory::getService('session');

		if (!$session->has('CSRFToken'))
		{
			$session->set('CSRFToken', md5(Factory::getService('security')->getRandom()->uuid()));
		}

		return $session->get('CSRFToken', null, $remove);
	}

	public static function checkToken($method = 'POST')
	{
		$request = Service::request();
		$xToken  = $request->getServer('HTTP_X_CSRF_TOKEN');
		$token   = static::getToken(false);

		if ($xToken)
		{
			return $xToken === $token;
		}

		if ('POST' === $method)
		{
			$isValid = $request->getPost($token) === '1';
		}
		else
		{
			$isValid = $request->get($token) === '1';
		}

		return $isValid;
	}
}
