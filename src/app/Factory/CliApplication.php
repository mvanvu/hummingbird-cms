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

	public function error(string $message, $context = null)
	{
		$this->console->error($message . PHP_EOL);
		$this->log($message, true, $context);
	}

	public function log(string $message, $error = false, $context = null)
	{
		$type       = $error ? 'error' : 'out';
		$stringData = ['message' => $message];
		$stringKey  = 'console-' . $type . '-msg';

		if ($context)
		{
			$stringData['context'] = $context;
			$stringKey             = 'console-context-' . $type . '-msg';
		}

		Log::addEntry($stringKey, $stringData, $this instanceof SocketApplication ? 'socket' : 'console');
	}

	public function out(string $message, $context = null)
	{
		$this->console->out($message . PHP_EOL);
		$this->log($message, false, $context);
	}
}