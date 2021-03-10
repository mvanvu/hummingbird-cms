<?php

namespace App\Helper;

use App\Mvc\Model\Log;
use App\Mvc\Model\QueueJob;
use App\Queue\QueueAbstract;
use ReflectionClass;
use ReflectionException;
use Throwable;

class Queue
{
	const PRIORITY_HIGH = 0;
	const PRIORITY_MEDIUM = 1;
	const PRIORITY_NORMAL = 2;
	const PRIORITY_LOW = 3;

	public static function add(string $handler, $payload = null, int $priority = Queue::PRIORITY_NORMAL)
	{
		if ($job = static::make($handler, $payload, $priority))
		{
			Console::getInstance()->executeQueue('--queueJob:' . $job->queueJobId);
		}

		return $job;
	}

	/**
	 * @param string $handler
	 * @param null   $payload
	 * @param int    $priority
	 *
	 * @return QueueJob|false
	 */

	public static function make(string $handler, $payload = null, int $priority = Queue::PRIORITY_NORMAL)
	{
		if (class_exists($handler))
		{
			try
			{
				$class = new ReflectionClass($handler);

				if ($class->isSubclassOf(QueueAbstract::class))
				{
					$priorities = [
						Queue::PRIORITY_LOW,
						Queue::PRIORITY_NORMAL,
						Queue::PRIORITY_MEDIUM,
						Queue::PRIORITY_HIGH,
					];

					if (!in_array($priority, $priorities))
					{
						$priority = Queue::PRIORITY_NORMAL;
					}

					$payload  = serialize($payload);
					$queueJob = new QueueJob;
					$queueJob->assign(
						[
							'queueJobId' => md5($handler . ':' . $payload . ':' . $priority . ':' . time()),
							'handler'    => $handler,
							'payload'    => $payload,
							'priority'   => $priority,
							'createdAt'  => Date::now('UTC')->toSql(),
							'createdBy'  => User::getActive()->id ?? 0,
							'handling'   => 'Y',
							'failedAt'   => null,
						]
					);

					if ($queueJob->save())
					{
						return $queueJob;
					}
				}
			}
			catch (ReflectionException $e)
			{
			}
		}

		return false;
	}

	public static function execute(string $handler, $payload = null, int $priority = Queue::PRIORITY_NORMAL)
	{
		if ($job = static::make($handler, $payload, $priority))
		{
			static::executeJob($job);
		}
		else
		{
			static::cliMessage('Failed. ' . $handler . ' return FALSE');
		}
	}

	public static function executeJob(QueueJob $job)
	{
		if (class_exists($job->handler))
		{
			try
			{
				$class = new ReflectionClass($job->handler);

				if ($class->isSubclassOf(QueueAbstract::class))
				{
					/** @var QueueAbstract $handler */
					$job->assign(['handling' => 'Y'])->save();
					$handler = new $job->handler;
					$handler->initJob($job);

					if ($handler->handle())
					{
						$job->delete();
						Log::addEntry('queue-completed', ['handler' => $job->queueJobId . ':' . $job->handler]);
						static::cliMessage('Completed. ' . $job->queueJobId . ':' . $job->handler);
					}
					else
					{
						$job->assign(['failedAt' => Date::now('UTC')->toSql(), 'handling' => 'N'])->save();
						Log::addEntry('queue-failed', ['handler' => $job->queueJobId . ':' . $job->handler]);
						static::cliMessage('Failed. ' . $job->queueJobId . ':' . $job->handler . ' return FALSE');
					}
				}
			}
			catch (Throwable $e)
			{
				$job->assign(['failedAt' => Date::now('UTC')->toSql(), 'handling' => 'N'])->save();
				Log::addEntry('queue-failed', ['handler' => get_class($e)]);
				static::cliMessage('Failed. ' . $e->getMessage());
			}
		}
	}

	public static function cliMessage(string $message, bool $error = false)
	{
		if (IS_CLI)
		{
			Console::getInstance()->{$error ? 'error' : 'out'}($message);
		}
	}

	public static function executeAll()
	{
		if (!IS_CLI)
		{
			// This should be executed in the CLI mode
			return;
		}

		$force = Console::getInstance()->hasArgument('force');
		$jobs  = $force ? QueueJob::find() : QueueJob::find(
			[
				'conditions' => 'failedAt IS NULL AND handling = :handling:',
				'order'      => 'priority ASC, createdAt ASC',
				'bind'       => ['handling' => 'N'],
			]
		);

		if ($jobs->count())
		{
			foreach ($jobs as $job)
			{
				static::executeJob($job);
			}
		}
	}
}