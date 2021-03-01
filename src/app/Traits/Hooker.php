<?php

namespace App\Traits;

trait Hooker
{
	protected static $callBackData = [];

	public static function getCallbackData(string $fromClass = null): array
	{
		return null === $fromClass ? static::$callBackData : (static::$callBackData[$fromClass] ?? []);
	}

	public function callback($callable, array $arguments = [])
	{
		$class = get_class($this);

		if (is_string($callable) && false === strpos($callable, '::'))
		{
			$callable = [$this, $callable];
		}

		static::$callBackData[$class][] = is_callable($callable) ? call_user_func_array($callable, $arguments) : null;

		return static::$callBackData[$class][count(static::$callBackData[$class]) - 1];
	}
}