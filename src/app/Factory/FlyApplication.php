<?php

declare(strict_types=1);

namespace App\Factory;

use Throwable;

class FlyApplication extends CliApplication
{
	public function execute()
	{
		try
		{
			foreach ($this->console->getArguments()->toArray() as $k => $v)
			{
				if (false !== strpos($k, ':'))
				{
					list($class, $argument) = explode(':', $k, 2);
					$fly = 'App\\Console\\Fly\\' . ucfirst($class);

					if (class_exists($fly) && is_callable($fly . '::execute'))
					{
						call_user_func_array($fly . '::execute', [$this, $argument]);
						break;
					}
				}
			}
		}
		catch (Throwable $e)
		{
			$this->error($e->getMessage());
		}
	}
}