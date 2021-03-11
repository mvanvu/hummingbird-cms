<?php

namespace App\Console\Fly;

use App\Console\Fly;
use App\Factory\FlyApplication;
use App\Helper\Queue;
use App\Mvc\Model\QueueJob as QueueJobModel;

class QueueJob implements Fly
{
	public function flap(FlyApplication $app, string $param = null)
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
		else
		{
			$app->getConsole()->error('No queues found.');
		}
	}
}