<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use MaiVu\Hummingbird\Lib\Factory;

class Text
{
	public static function _($string, $placeholders = null)
	{
		return Language::_($string, $placeholders);
	}

	public static function plural($string, $count, $placeholders = null)
	{
		if (Language::getActiveLanguage()->has('content.' . $string . '-' . $count))
		{
			return self::_($string . '-' . $count, $placeholders);
		}

		return self::_($string, $placeholders);
	}

	public static function fetchJsData()
	{
		static $fetched = false;

		if (!$fetched)
		{
			$fetched = true;
			Factory::getService('assets')->addInlineJs('cmsCore.language.fetch()');
		}
	}
}