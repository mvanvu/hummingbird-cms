<?php

declare(strict_types=1);

namespace App\Factory;

use App\Helper\Console;
use App\Helper\Event;
use App\Mvc\Model\Log;
use Phalcon\Application\AbstractApplication;
use Phalcon\Di\DiInterface;
use Throwable;

class CliApplication extends AbstractApplication
{
	protected $console;

	public function __construct(DiInterface $container = null)
	{
		parent::__construct($container);
		$this->console = Console::getInstance();
	}

	public function getConsole()
	{
		return $this->console;
	}

	public function execute()
	{
		try
		{
			Event::trigger('onBootCli', [$this], ['Cli']);
		}
		catch (Throwable $throwable)
		{
			$this->error($throwable->getMessage());
		}
	}

	public function error(string $message, string $context = null, bool $log = false)
	{
		$this->console->error($message);
		$log && $this->log($message, $context);
	}

	public function log(string $message, string $context = null)
	{
		Log::addEntry($message, $context ?? ($this instanceof FlyApplication ? 'fly' : 'console'));
	}

	public function out(string $message, string $context = null, bool $log = false)
	{
		$this->console->out($message);
		$log && $this->log($message, $context);
	}
}