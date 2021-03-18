<?php

namespace App\Helper;

use App\Mvc\Model\Currency as CurrencyModel;

class Currency
{
	/**
	 * @var CurrencyModel | null
	 */
	protected static $main = null;

	public static function setActiveCode(string $code): bool
	{
		if (isset(static::load()[$code]))
		{
			State::set('currency.active', $code);

			return true;
		}

		return false;
	}

	public static function load()
	{
		static $currencies = null;

		if (null === $currencies)
		{
			$currencies = [];
			$main       = Config::get('mainCurrency', 'USD');

			foreach (CurrencyModel::find('state = \'P\'') as $currency)
			{
				$currencies[$currency->code] = $currency;

				if ($main === $currency->code)
				{
					static::$main = $currency;
				}
			}
		}

		return $currencies;
	}

	public static function format($number): string
	{
		$active = static::getActive();

		if (static::$main instanceof CurrencyModel)
		{
			if ($active && static::$main->code !== $active->code)
			{
				return $active->format((float) $number * (float) $active->rate);
			}

			return static::$main->format($number);
		}

		return (string) $number;
	}

	public static function getActive()
	{
		$code = static::getActiveCode();

		return static::load()[$code] ?? null;
	}

	public static function getActiveCode(): string
	{
		return State::get('currency.active', Config::get('mainCurrency', 'USD'));
	}
}