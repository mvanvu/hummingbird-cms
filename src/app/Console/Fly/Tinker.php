<?php

namespace App\Console\Fly;

use App\Console\Fly;
use App\Factory\FlyApplication;
use App\Helper\Queue;
use App\Queue\Composer;
use Psy\Shell;

class Tinker implements Fly
{
	public function flap(FlyApplication $app, string $param = null)
	{
		if (class_exists('Psy\\Shell'))
		{
			(new Shell)->run();
		}
		else
		{
			$app->getConsole()->out('Psy\\Shell not found. Starting to install Psy\\Shell using Composer...');

			if (Queue::execute(Composer::class, ['commands' => 'require psy/psysh', 'pathToJson' => BASE_PATH]))
			{
				$app->getConsole()->outLn('Psy\\Shell installed. Please restart the Tinker Fly to continue.');
				exit(0);
			}
		}
	}
}