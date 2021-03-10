<?php

declare(strict_types=1);

namespace App\Factory;

use App\Console\Fly;
use App\Helper\Constant;
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
					list($class, $param) = explode(':', $k, 2);
					$ns = Constant::getNamespaceFly(ucfirst($class));

					if (class_exists($ns) && ($fly = new $ns) instanceof Fly)
					{
						$fly->execute($this, $param);
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