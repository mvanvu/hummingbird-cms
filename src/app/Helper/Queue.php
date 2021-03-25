<?php

namespace App\Helper;

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

	/**
	 * Direct execute the queue job by Fly application using PHP shell_exec
	 *
	 * @param string $handler
	 * @param null   $payload
	 * @param int    $priority
	 *
	 * @return QueueJob|false
	 */

	public static function add(string $handler, $payload = null, int $priority = Queue::PRIORITY_NORMAL)
	{
		$state = function_exists('shell_exec') ? 'HANDLING' : 'SCHEDULED';
		$job   = static::make($handler, $payload, $priority, $state);

		if ($job && 'HANDLING' === $state)
		{
			Console::getInstance()->execute('queueJob:' . $job->queueJobId);
		}

		return $job;
	}

	/**
	 * @param string $handler
	 * @param null   $payload
	 * @param int    $priority
	 * @param string $state
	 *
	 * @return QueueJob|false
	 */

	public static function make(string $handler, $payload = null, int $priority = Queue::PRIORITY_NORMAL, string $state = 'HANDLING')
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
							'queueJobId' => md5($handler . ':' . $payload . ':' . (QueueJob::count() + 1)),
							'handler'    => $handler,
							'payload'    => $payload,
							'priority'   => $priority,
							'createdAt'  => Date::now('UTC')->toSql(),
							'createdBy'  => User::id(),
							'state'      => in_array($state, ['HANDLING', 'SCHEDULED', 'FAILED']) ? $state : 'HANDLING',
							'attempts'   => 0,
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

	/**
	 * Schedule the queue job under the cron tab
	 *
	 * @param string $handler
	 * @param null   $payload
	 * @param int    $priority
	 *
	 * @return QueueJob|false
	 */

	public static function schedule(string $handler, $payload = null, int $priority = Queue::PRIORITY_NORMAL)
	{
		return static::make($handler, $payload, $priority, 'SCHEDULED');
	}

	public static function execute(string $handler, $payload = null, int $priority = Queue::PRIORITY_NORMAL): bool
	{
		$job = static::make($handler, $payload, $priority);

		if (!$job || !static::executeJob($job))
		{
			static::cliMessage('Failed. ' . $handler . ' return FALSE');

			return false;
		}

		return true;
	}

	public static function executeJob(QueueJob $job): bool
	{
		if (class_exists($job->handler))
		{
			try
			{
				$class = new ReflectionClass($job->handler);

				if ($class->isSubclassOf(QueueAbstract::class))
				{
					/** @var QueueAbstract $handler */
					$handler = new $job->handler;
					$handler->initJob($job);

					if ($handler->handle())
					{
						static::cliMessage('Queue job completed: ' . $job->handler);
						$job->delete();
					}
					else
					{
						static::cliMessage('Queue job failed: ' . $job->handler . ' return FALSE');
						$job->assign(
							[
								'failedAt' => Date::now('UTC')->toSql(),
								'attempts' => (int) $job->attempts + 1,
								'state'    => 'FAILED',
							]
						)->save();
					}

					return true;
				}
			}
			catch (Throwable $e)
			{
				static::cliMessage('Failed. ' . $e->getMessage());
				$job->assign(
					[
						'failedAt' => Date::now('UTC')->toSql(),
						'attempts' => (int) $job->attempts + 1,
						'state'    => 'FAILED',
					]
				)->save();
			}
		}

		return false;
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
		$jobs  = $force ? QueueJob::find() : QueueJob::find('state <> \'HANDLING\'');

		if ($jobs->count())
		{
			foreach ($jobs as $job)
			{
				static::executeJob($job);
			}
		}
	}
}