<?php

namespace App\Helper;

use MaiVu\Php\Registry;

class State
{
	protected static $marks = [];

	public static function get($key, $default = null, $remove = false, $shared = false)
	{
		if (!$shared)
		{
			$key = Uri::getClient() . '.' . $key;
		}

		return Service::session()->get($key, $default, $remove);
	}

	public static function getById($sessionId): Registry
	{
		$returnData = Registry::create();
		$db         = Service::db();

		// Purge if it's expired
		$db->delete(
			Database::table('sessions'),
			'id = ? AND time < ?',
			[$sessionId, time() - (int) ini_get('session.gc_maxlifetime')]
		);

		if ($sessionData = Service::session()->getAdapter()->read($sessionId))
		{
			$offset = 0;

			while ($offset < strlen($sessionData))
			{
				if (!strstr(substr($sessionData, $offset), '|'))
				{
					return $returnData;
				}

				$pos    = strpos($sessionData, '|', $offset);
				$num    = $pos - $offset;
				$key    = substr($sessionData, $offset, $num);
				$offset += $num + 1;
				$data   = unserialize(substr($sessionData, $offset));
				$returnData->set($key, $data);
				$offset += strlen(serialize($data));
			}
		}

		return $returnData;
	}

	public static function set($key, $value = null, $shared = false)
	{
		if (!$shared)
		{
			$key = Uri::getClient() . '.' . $key;
		}

		Service::session()->set($key, $value);
	}

	public static function remove($key, $shared = false)
	{
		if (!$shared)
		{
			$key = Uri::getClient() . '.' . $key;
		}

		Service::session()->remove($key);
	}

	public static function getMark($mark, $default = null)
	{
		return array_key_exists($mark, static::$marks) ? static::$marks[$mark] : $default;
	}

	public static function setMark($mark, $value, $override = true)
	{
		if ($override || !array_key_exists($mark, static::$marks))
		{
			static::$marks[$mark] = $value;
		}
	}

	public static function gc()
	{
		$session = Service::session();

		if (!$session->exists())
		{
			$session->start();
		}

		session_gc();
	}
}