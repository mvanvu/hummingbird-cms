<?php

namespace App\Console\Fly;

use App\Console\Fly;
use App\Factory\FlyApplication;
use App\Helper\Event;

class Plugin implements Fly
{
	public function flap(FlyApplication $app, string $param = null)
	{
		if (strpos($param, '/'))
		{
			list($group, $name) = explode('/', $param, 2);

			if ($handler = Event::getHandlerByGroupName($group, $name))
			{
				if ($queueJobId = $app->getConsole()->getArgument('queueJobId'))
				{
					(new QueueJob)->flap($app, $queueJobId);
				}
				else
				{
					$handler->callback('onSky', [$app, $this]);
				}
			}
		}
	}
}