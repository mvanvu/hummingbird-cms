<?php

declare(strict_types=1);

namespace App\Factory;

use App\Console\Fly;
use App\Helper\Constant;
use Throwable;

class FlyApplication extends CliApplication
{
	/**
	 * @var Fly
	 */
	protected $fly;

	public function getFly(): Fly
	{
		return $this->fly;
	}

	public function execute()
	{
		try
		{
			foreach ($this->console->getArguments()->toArray() as $k => $v)
			{
				if (false === strpos($k, ':'))
				{
					$ns    = Constant::getNamespaceFly(ucfirst($k));
					$param = null;
				}
				else
				{
					list($class, $param) = explode(':', $k, 2);
					$ns = Constant::getNamespaceFly(ucfirst($class));
				}

				if (class_exists($ns) && ($this->fly = new $ns) instanceof Fly)
				{
					$this->fly->flap($this, $param);

					// Fly one time only
					$this->console->outLn('Landed on [' . $ns . '] command: ' . implode(' ', ($_SERVER['argv'] ?? [])));
					break;
				}
			}
		}
		catch (Throwable $e)
		{
			$this->error($e->getMessage());
		}
	}
}