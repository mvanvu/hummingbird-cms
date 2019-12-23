<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use Phalcon\Http\Request;
use Phalcon\Session\Manager as SessionAdapter;
use MaiVu\Hummingbird\Lib\Factory;

class Form
{
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

	public static function tokenInput()
	{
		return '<input type="hidden" name="' . self::getToken() . '" value="1"/>';
	}

	public static function checkToken($method = 'POST')
	{
		/**
		 * @var Request $request
		 */
		$request = Factory::getService('request');
		$xToken  = $request->getServer('HTTP_X_CSRF_TOKEN');
		$remove  = $request->isAjax() || $xToken ? false : true;
		$token   = self::getToken($remove);

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

		if (!$isValid)
		{
			Factory::getService('flashSession')->warning(Text::_('invalid-token-notice'));
		}

		return $isValid;
	}
}
