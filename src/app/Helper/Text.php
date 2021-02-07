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

		if (Language::getActiveLanguage()->has('content.' . $string . '-' . $count))
		{
			return static::_($string . '-' . $count, $placeholders);
		}

		return static::_($string, $placeholders);
	}

	public static function _($string, array $placeholders = null)
	{
		return Language::_($string, $placeholders);
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