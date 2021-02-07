<?php

namespace App\Queue;

use App\Factory\CliApplication;
use App\Factory\Factory;
use App\Mvc\Model\QueueJob;
use Throwable;

abstract class QueueAbstract
{
	/**
	 * @var CliApplication
	 */
	protected $app;

	/**
	 * @var QueueJob
	 */
	protected $job;

	/**
	 * @var mixed
	 */
	protected $data;

	public final function initJob(QueueJob $job)
	{
		$this->job  = $job;
		$this->app  = Factory::getApplication();
		$this->data = unserialize($job->payload);
	}

	/**
	 *
	 * @return bool
	 * @throws Throwable
	 */
	abstract public function handle(): bool;
}