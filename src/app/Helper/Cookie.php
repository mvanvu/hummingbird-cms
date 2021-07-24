<?php

namespace App\Helper;

class Cookie
{
	public static function get(string $name, $default = null)
	{
		$name = md5($name);

		return !IS_CLI && isset($_COOKIE[$name]) ? unserialize($_COOKIE[$name]) : $default;
	}

	public static function set(string $name, $value)
	{
		if (!IS_CLI)
		{
			setcookie(
				md5($name),
				serialize($value), time() + (86400 * 30),
				'/',
				'',
				Uri::getCurrentHttpSchema() === 'https://',
				true
			); // 30 days);
		}
	}

	public static function has(string $name): bool
	{
		return !IS_CLI && isset($_COOKIE[md5($name)]);
	}

	public static function remove(string $name)
	{
		$name = md5($name);

		if (!IS_CLI && isset($_COOKIE[$name]))
		{
			setcookie($name, '', time() - 3600);
		}
	}
}