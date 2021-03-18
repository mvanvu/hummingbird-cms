<?php

namespace App\Helper;

use DateInterval;
use DateTime;
use DateTimeZone;

class Date extends DateTime
{
	/** @var DateTimeZone */
	protected static $stz = null;

	public function __construct($time = 'now', $tz = null)
	{
		if (null === static::$stz)
		{
			static::$stz = IS_CLI ? new DateTimeZone(Config::get('timezone', 'UTC')) : User::getActive()->getTimezone();
		}

		if (!($tz instanceof DateTimeZone))
		{
			if (null === $tz)
			{
				$tz = static::$stz;
			}
			elseif (is_string($tz))
			{
				$tz = new DateTimeZone($tz);
			}
		}

		parent::__construct($time, $tz);
	}

	public static function relative($date, $time = null, $unit = null)
	{
		if (!($date instanceof Date))
		{
			$date = static::fromDB($date);
		}

		if (!($time instanceof Date))
		{
			$time = static::now('UTC');
		}

		$diff = $time->toUnix() - $date->toUnix();

		// Less than a minute
		if ($diff < 60)
		{
			return Text::_('less-than-a-minute-ago');
		}

		// Round to minutes
		$diff = round($diff / 60);
		// 1 to 59 minutes

		if ($diff < 60 || $unit === 'minute')
		{
			return Text::_('the-minute' . ($diff > 1 ? 's' : '') . '-ago', ['minutes' => $diff]);
		}

		// Round to hours
		$diff = round($diff / 60);
		// 1 to 23 hours

		if ($diff < 24 || $unit === 'hour')
		{
			return Text::_('the-hour' . ($diff > 1 ? 's' : '') . '-ago', ['hours' => $diff]);
		}

		// Round to days
		$diff = round($diff / 24);
		// 1 to 6 days

		if ($diff < 7 || $unit === 'day')
		{
			return Text::_('the-day' . ($diff > 1 ? 's' : '') . '-ago', ['days' => $diff]);
		}

		// Round to weeks
		$diff = round($diff / 7);
		// 1 to 4 weeks

		if ($diff <= 4 || $unit === 'week')
		{
			return Text::_('the-week' . ($diff > 1 ? 's' : '') . '-ago', ['weeks' => $diff]);
		}

		// Over a month, return the absolute time
		return $date->toDisplay();
	}

	public static function fromDB($time = 'now')
	{
		return static::getInstance($time, 'UTC');
	}

	public static function getInstance($time = 'now', $tz = null)
	{
		return new Date($time, $tz);
	}

	public static function now($tz = null)
	{
		return static::getInstance('now', $tz);
	}

	public function toUnix()
	{
		return (int) parent::format('U');
	}

	public function toDisplay(string $format = null, bool $translate = true)
	{
		$formattedDate = $this->toFormat($format);

		if ($translate)
		{
			preg_match_all('/[a-zA-Z]+/', $formattedDate, $matches);

			if (!empty($matches[0]))
			{
				$language = Language::getActiveLanguage();

				foreach ($matches[0] as $string)
				{
					$index = 'locale-' . strtolower($string . (strcasecmp('may', $string) === 0 ? '-short' : ''));

					if ($language->has($index))
					{
						$formattedDate = str_ireplace($string, strtolower($language->get($index)), $formattedDate);
					}
				}

				$formattedDate = ucfirst($formattedDate);
			}
		}

		return $formattedDate;
	}

	public function toFormat($format = null)
	{
		if (null === $format)
		{
			$format = Text::_('@params.dateTimeFormat');
		}

		$tz   = IS_CLI ? static::$stz : User::getActive()->getTimezone();
		$stz  = parent::getTimezone();
		$diff = $tz->getName() !== $stz->getName();

		if ($diff)
		{
			parent::setTimezone($tz);
		}

		$formattedDate = parent::format($format);

		if ($diff)
		{
			parent::setTimezone($stz);
		}

		return $formattedDate;
	}

	public static function yesterday($tz = null)
	{
		return static::now($tz)->sub(new DateInterval('P1D'));
	}

	public static function tomorrow($tz = null)
	{
		return static::now($tz)->add(new DateInterval('P1D'));
	}

	public function toSql()
	{
		$stz    = parent::getTimezone();
		$notUTC = 'UTC' !== $stz->getName();

		if ($notUTC)
		{
			parent::setTimezone(new DateTimeZone('UTC'));
		}

		$sqlDate = parent::format('Y-m-d H:i:s');

		if ($notUTC)
		{
			parent::setTimezone($stz);
		}

		return $sqlDate;
	}

	public function __toString()
	{
		return $this->toDisplay();
	}
}
