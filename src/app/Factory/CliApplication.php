<?php

declare(strict_types=1);

namespace App\Factory;

use App\Helper\Console;
use App\Helper\Event;
use App\Helper\Queue;
use App\Mvc\Model\Log;
use App\Mvc\Model\QueueJob;
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
			if (!$this->console->hasArgument('skip-plugins'))
			{
				Event::trigger('onBootCli', [$this], ['Cli']);
			}

			if ($queueJobId = $this->console->getArgument('queueJobId'))
			{
				if ($queueJobId === 'all')
				{
					Queue::executeAll();
				}
				elseif ($queueJob = QueueJob::findFirst(['queueJobId = :queueJobId:', 'bind' => ['queueJobId' => $queueJobId]]))
				{
					/** @var QueueJob $queueJob */
					Queue::executeJob($queueJob);
				}
			}
		}
		catch (Throwable $throwable)
		{
			$this->error($throwable->getMessage());
		}
	}

	public function error(string $message, $context = null)
	{
		$this->console->error($message);
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
		$this->console->out($message);
		$this->log($message, false, $context);
	}
}