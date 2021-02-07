<?php

namespace App\Traits;

trait Hooker
{
	public function callback(string $method, array $arguments = [])
	{
		$callable = [$this, $method];

		return is_callable($callable) ? call_user_func_array($callable, $arguments) : null;
	}
}