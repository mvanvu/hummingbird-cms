<?php

declare(strict_types=1);

namespace App\Factory;

use App\Console\Fly;
use App\Helper\Console;
use App\Helper\Constant;
use App\Helper\Date;
use App\Mvc\Model\Log;
use Phalcon\Application\AbstractApplication;
use Phalcon\Di\DiInterface;
use Throwable;

class FlyApplication extends AbstractApplication
{
	/**
	 * @var Fly
	 */
	protected $fly;

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var Console
	 */
	protected $console;

	public function __construct(DiInterface $container = null)
	{
		parent::__construct($container);
		$this->console = Console::getInstance();
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function getFly(): Fly
	{
		return $this->fly;
	}

	public function getConsole()
	{
		return $this->console;
	}

	public function execute()
	{
		try
		{
			$landed = false;

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
					$landed        = true;
					$this->message = Date::now('UTC')->format('Y-m-d H:i:s') . '-UTC Landed on [' . $ns . '] command: ' . implode(' ', ($_SERVER['argv'] ?? []));
					$this->fly->flap($this, $param);

					// Fly one time only
					$this->console->outLn($this->message);
					break;
				}
			}

			if (!$landed)
			{
				$this->console->runCallbacks();
			}
		}
		catch (Throwable $e)
		{
			$this->error($e->getMessage());
		}
	}

	public function error(string $message, string $context = null, bool $log = false)
	{
		$this->console->error($message);
		$log && $this->log($message, $context);
	}

	public function log(string $message, string $context = null)
	{
		Log::addEntry($message, $context ?? 'fly.system');
	}

	public function out(string $message, string $context = null, bool $log = false)
	{
		$this->console->out($message);
		$log && $this->log($message, $context);
	}
}