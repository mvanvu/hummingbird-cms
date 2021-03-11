<?php

namespace App\Console\Fly;

use App\Console\Fly;
use App\Factory\FlyApplication;
use App\Helper\Console;
use App\Helper\Date;
use App\Helper\Event;
use App\Plugin\Plugin as AppPlugin;

class Schedule implements Fly
{
	protected $startTime;

	protected $runTime;

	protected $sleep;

	protected $usleep;

	protected $callable;

	public function __construct()
	{
		set_time_limit(0);
		ini_set('memory_limit', -1);
		$console         = Console::getInstance();
		$this->startTime = Date::now('UTC');
		$this->sleep     = $console->getArgument('sleep', 1, 'uint');
		$this->usleep    = $console->getArgument('usleep', 100, 'uint');
		$this->usleep    = $console->getArgument('usleep', 100, 'uint');

		if ($callable = $console->getArgument('callable'))
		{
			if (is_callable($callable))
			{
				$this->callable = $callable;
			}
			elseif (false !== strpos($callable, '/'))
			{
				list($group, $name) = explode('/', $callable, 2);

				if ($handler = Event::getHandlerByGroupName($group, $name))
				{
					$this->callable = $handler;
				}
			}
		}
	}

	public function flap(FlyApplication $app, string $param = null)
	{
		/**
		 * **************************************************************************
		 * ********************* PARAM FORMAT EXAMPLE (UTC) *************************
		 * **************************************************************************
		 * Run the task every 5 seconds                             = 5s            *
		 * Run the task every 1 minute                              = 1i            *
		 * Run the task every 1 hour                                = 1h            *
		 * Run the task every daily at time                         = t17:00        *
		 * **************************************************************************
		 * Using Plugin Fly for a custom advance schedule                           *
		 * **************************************************************************
		 */

		while (true)
		{
			usleep($this->usleep);
			$this->runTime = Date::now('UTC');

			if ($this->callable)
			{
				if (preg_match('/^([0-9]+)([sih])$/', $param, $matches))
				{
					$time = $this->runTime->toUnix() - $this->startTime->toUnix();
					$num  = (int) $matches[1];

					switch ($matches[2])
					{
						case 'i':
							$num *= 60;
							break;

						case 'h':
							$num *= 3600;
							break;
					}

					if ($time > 0 && $time % $num === 0)
					{
						if ($this->callable instanceof AppPlugin)
						{
							$this->callable->callback('onSchedule', [$app, $this]);
						}
						else
						{
							call_user_func($this->callable);
						}

						break;
					}
				}
				elseif (preg_match('/^t([0-9]{2}:[0-9]{2})$/', $param, $matches))
				{
					if ($this->runTime->format('H:i:s') === $matches[1] . ':00')
					{
						if ($this->callable instanceof AppPlugin)
						{
							$this->callable->callback('onSchedule', [$app, $this]);
						}
						else
						{
							call_user_func($this->callable);
						}

						break;
					}
				}
				elseif ($this->callable instanceof AppPlugin)
				{
					$this->callable->callback('onSchedule', [$app, $this]);
				}
			}
		}

		sleep($this->sleep);

		// Restart loop
		$this->flap($app, $param);
	}

	public function getStartTime(): Date
	{
		return $this->startTime;
	}

	public function getRunTime(): Date
	{
		return $this->runTime;
	}
}