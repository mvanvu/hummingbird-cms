<?php

namespace App\Console\Fly;

use App\Factory\FlyApplication;
use App\Helper\Event;

class Plugin
{
	public static function execute(FlyApplication $app, string $argument)
	{
		if (strpos($argument, '/'))
		{
			list($group, $name) = explode('/', $argument, 2);

			if ($handler = Event::getHandlerByGroupName($group, $name))
			{
				$handler->callback('onFly', [$app]);
			}
		}
	}
}