<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use Phalcon\Session\Adapter\Files as PhalconSession;
use MaiVu\Hummingbird\Lib\Factory;

class State
{
	protected static $marks = [];

	/**
	 * @return PhalconSession
	 */
	public static function getSession()
	{
		return Factory::getService('session');
	}

	public static function get($key, $default = null, $remove = false, $shared = false)
	{
		if (!$shared)
		{
			$key = Uri::getActive()->getVar('client') . '.' . $key;
		}

		return self::getSession()->get($key, $default, $remove);
	}

	public static function set($key, $value = null, $shared = false)
	{
		if (!$shared)
		{
			$key = Uri::getActive()->getVar('client') . '.' . $key;
		}

		self::getSession()->set($key, $value);
	}

	public static function remove($key, $shared = false)
	{
		if (!$shared)
		{
			$key = Uri::getActive()->getVar('client') . '.' . $key;
		}

		self::getSession()->remove($key);
	}

	public static function getMark($mark, $default = null)
	{
		return array_key_exists($mark, self::$marks) ? self::$marks[$mark] : $default;
	}

	public static function setMark($mark, $value, $override = true)
	{
		if ($override || !array_key_exists($mark, self::$marks))
		{
			self::$marks[$mark] = $value;
		}
	}
}