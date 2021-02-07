<?php

namespace App\Helper;

if (version_compare(PHP_VERSION, '5.6', '>='))
{
	@ini_set('default_charset', 'UTF-8');
}
else
{
	// Check if mbstring extension is loaded and attempt to load it if not present except for windows
	if (extension_loaded('mbstring'))
	{
		@ini_set('mbstring.internal_encoding', 'UTF-8');
		@ini_set('mbstring.http_input', 'UTF-8');
		@ini_set('mbstring.http_output', 'UTF-8');
	}

	// Same for iconv
	if (function_exists('iconv'))
	{
		iconv_set_encoding('internal_encoding', 'UTF-8');
		iconv_set_encoding('input_encoding', 'UTF-8');
		iconv_set_encoding('output_encoding', 'UTF-8');
	}
}

class StringHelper
{
	public static function strlen($string)
	{
		return mb_strlen($string);
	}

	public static function substr($string, $offset, $length = false)
	{
		if (false === $length)
		{
			return mb_substr($string, $offset);
		}

		return mb_substr($string, $offset, $length);
	}

	public static function increment($string, $n = 0)
	{
		$search    = $replace = '#-(\d+)$#';
		$newFormat = $oldFormat = '-%d';

		if (preg_match($search, $string, $matches))
		{
			$n      = empty($n) ? ($matches[1] + 1) : $n;
			$string = preg_replace($replace, sprintf($oldFormat, $n), $string);
		}
		else
		{
			$n      = empty($n) ? 2 : $n;
			$string .= sprintf($newFormat, $n);
		}

		return $string;
	}

	public static function truncate($string, $length = 100)
	{
		$string = strip_tags($string);
		$len    = static::strlen($string);

		if ($length >= $len)
		{
			return $string;
		}

		return static::substr($string, 0, $length) . '...';
	}
}