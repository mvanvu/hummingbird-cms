<?php

namespace App\Console\Fly;

use App\Console\Fly;
use App\Factory\FlyApplication;
use App\Helper\Event;

class Plugin implements Fly
{
	public function execute(FlyApplication $app, string $param)
	{
		if (strpos($param, '/'))
		{
			list($group, $name) = explode('/', $param, 2);

			if ($handler = Event::getHandlerByGroupName($group, $name))
			{
				if ($queueJobId = $app->getConsole()->getArgument('queueJobId'))
				{
					(new QueueJob)->execute($app, $queueJobId);
				}
				else
				{
					$handler->callback('onSky', [$app]);
				}
			}
		}
	}
}