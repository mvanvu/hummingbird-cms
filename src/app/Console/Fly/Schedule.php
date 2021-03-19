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
	protected $time;

	protected $runTime;

	protected $sleep;

	protected $usleep;

	protected $callable;

	protected $tz;

	public function __construct()
	{
		set_time_limit(0);
		ini_set('memory_limit', -1);
		ignore_user_abort(true);
		$console    = Console::getInstance();
		$this->tz   = $console->getArgument('timezone', 'UTC');
		$this->time = Date::getInstance($console->getArgument('time', 'now'), $this->tz);

		if ($callable = $console->getArgument('callback'))
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
		 * Run the task every 5 seconds                             = s:5           *
		 * Run the task every 1 minute                              = i:1           *
		 * Run the task every 1 hour                                = h:1           *
		 * Run the task every daily at time                         = t:17:00       *
		 * **************************************************************************
		 * Using Plugin Fly for a custom advance schedule                           *
		 * **************************************************************************
		 */

		while (true)
		{
			$this->runTime = Date::now($this->tz);

			if ($this->callable)
			{
				if (preg_match('/^([sih]):([0-9]+)$/', $param, $matches))
				{
					$time = $this->runTime->toUnix() - $this->time->toUnix();
					$num  = (int) $matches[2];

					switch ($matches[1])
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
						$this->callback($app);
					}
				}
				elseif (preg_match('/^t:([0-9]{2}:[0-9]{2})$/', $param, $matches))
				{
					if ($this->runTime->format('H:i:s') === $matches[1] . ':00')
					{
						$this->callback($app);
					}
				}
				else
				{
					$this->callback($app);
				}
			}

			sleep(1); // Restart after 1 second
		}
	}

	protected function callback(FlyApplication $app)
	{
		if ($this->callable instanceof AppPlugin)
		{
			$this->callable->callback('onSchedule', [$app, $this]);
		}
		else
		{
			call_user_func($this->callable);
		}

		if ($app->getConsole()->hasArgument('log'))
		{
			$app->log($app->getMessage(), 'fly.schedule');
		}
	}

	public function getStartTime(): Date
	{
		return $this->time;
	}

	public function getRunTime(): Date
	{
		return $this->runTime;
	}
}