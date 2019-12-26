<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use DateTime, DateTimeZone;

class Date extends DateTime
{
	/** @var DateTimeZone */
	protected static $gmt = null;

	/** @var DateTimeZone */
	protected static $stz = null;

	public function __construct($time = 'now', $tz = null)
	{
		if (null === self::$gmt)
		{
			self::$gmt = new DateTimeZone('GMT');
		}

		if (null === self::$stz)
		{
			self::$stz = new DateTimeZone(@date_default_timezone_get());
		}

		if (!($tz instanceof DateTimeZone))
		{
			if ($tz === null)
			{
				$tz = self::$gmt;
			}
			elseif (is_string($tz))
			{
				$tz = new DateTimeZone($tz);
			}
		}

		parent::__construct($time, $tz);
	}


	public static function getInstance($time = 'now', $tz = null)
	{
		return new Date($time, $tz);
	}

	public function toFormat($format = null)
	{
		if (null === $format)
		{
			$format = Text::_('locale.date-time-format');
		}

		$tz   = User::getInstance()->getTimezone();
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

	public function toDisplay($format = null, $translate = true)
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
					$index = 'locale.' . strtolower($string . ('may' === $string ? '-short' : ''));

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

	public function toUnix()
	{
		return (int) parent::format('U');
	}

	public function toSql()
	{
		$stz  = parent::getTimezone();
		$utc  = 'UTC' === $stz->getName();

		if (!$utc)
		{
			parent::setTimezone('UTC');
		}

		$sqlDate = parent::format('Y-m-d H:i:s');

		if (!$utc)
		{
			parent::setTimezone($stz);
		}

		return $sqlDate;
	}

	public static function relative($date, $time = null, $unit = null)
	{
		if (!($date instanceof Date))
		{
			$date = static::getInstance($date, new DateTimeZone('UTC'));
		}

		if (!($time instanceof Date))
		{
			$time = static::getInstance('now', new DateTimeZone('UTC'));
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

	public function __toString()
	{
		return $this->toDisplay();
	}
}
