<?php

namespace App\Helper;

class Text
{
	protected static $strings = [];

	public static function plural($string, $count, array $placeholders = null)
	{
		if (null === $placeholders)
		{
			$placeholders = ['count' => $count];
		}
		elseif (!isset($placeholders['count']))
		{
			$placeholders['count'] = $count;
		}

		if (Language::getActiveLanguage()->has('strings.' . $string . '-' . $count))
		{
			return static::_($string . '-' . $count, $placeholders);
		}

		return static::_($string, $placeholders);
	}

	public static function _(string $string, array $placeholders = null)
	{
		return str_replace(
			['_EOL_', '_BR_', '_Q_', '_QQ_'],
			[PHP_EOL, '<br>', '\'', '"'],
			Language::_($string, $placeholders)
		);
	}

	public static function script(string $string)
	{
		if (!isset(static::$strings[$string]))
		{
			static::$strings[$string] = static::_($string);
		}
	}

	public static function scripts()
	{
		if (static::$strings)
		{
			Assets::inlineJs('cmsCore.language.load(' . json_encode(static::$strings) . ');');
			static::$strings = [];
		}
	}
}