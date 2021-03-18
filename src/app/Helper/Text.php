<?php

namespace App\Helper;

class Text
{
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

	public static function fetchJsData()
	{
		static $fetched = false;

		if (!$fetched)
		{
			$fetched = true;
			Assets::inlineJs('cmsCore.language.fetch();');
		}
	}
}