<?php

namespace App\Console\Fly;

use App\Console\Fly;
use App\Factory\FlyApplication;
use App\Helper\Queue;
use App\Mvc\Model\QueueJob as QueueJobModel;

class QueueJob implements Fly
{
	public function execute(FlyApplication $app, string $param)
	{
		if ($param === 'all')
		{
			Queue::executeAll();
		}
		elseif ($queueJob = QueueJobModel::findFirst(['queueJobId = :queueJobId:', 'bind' => ['queueJobId' => $param]]))
		{
			/**
			 * @var QueueJobModel $queueJob
			 */
			Queue::executeJob($queueJob);
		}
	}
}