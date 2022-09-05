<?php

namespace MaiVu\Php;

use Closure;

class Filter
{
	protected static $rules = [];

	public static function setRule($name, Closure $handler)
	{
		static::$rules[$name] = $handler;
	}

	public static function cleanArray(array $values, $type = 'string')
	{
		$result = [];

		foreach ($values as $value) {
			$result[] = static::clean($value, $type);
		}

		return $result;
	}

	public static function clean($value, $type = 'string')
	{
		if (is_array($type)) {
			$result = $value;

			foreach ($type as $filterType) {
				$result = static::clean($result, $filterType);
			}

			return $result;
		}

		if (preg_match('/:array$/', $type)) {
			return static::cleanArray((array) $value, preg_replace('/:array$/', '', $type));
		}

		switch ($type) {
			case 'int':
			case 'uint':
			case 'float':
			case 'ufloat':
			case 'double':
			case 'udouble':
				$callback = in_array($type, ['int', 'uint']) ? 'intval' : 'floatval';
				$result   = $callback($value);

				if (strpos($type, 'u') === 0) {
					$result = abs($result);
				}

				break;

			case 'alphaNum':
			case 'base64':
				$pattern = 'alphaNum' === $type ? '/[^A-Z0-9]/i' : '/[^A-Z0-9\/+=]/i';
				$result  = (string) preg_replace($pattern, '', $value);
				break;

			case 'string':
			case 'email':
			case 'url':
			case 'encode':

				$filterMaps = [
					// 'string' => FILTER_SANITIZE_STRING,
					'email'  => FILTER_SANITIZE_EMAIL,
					'url'    => FILTER_SANITIZE_URL,
					'encode' => FILTER_SANITIZE_ENCODED,
				];

				if ('string' === $type) {
					$result = htmlspecialchars($value);
				} else {
					$result = filter_var($value, $filterMaps[$type]);
				}

				break;

			case 'slug':
			case 'path':
				$callback = static::class . '::to' . ucfirst($type);
				$result   = $callback($value);
				break;

			case 'unset':
				$result = null;
				break;

			case 'jsonEncode':
				$result = json_encode($value);
				break;

			case 'jsonDecode':
				$result = is_array($value) ? $value : (json_decode($value, true) ?: []);
				break;

			case 'yesNo':
			case 'yes|no':
				$result = in_array($value, ['Y', 'N'], true) ? $value : 'N';
				break;

			case 'YES|NO':
				$result = in_array($value, ['YES', 'NO'], true) ? $value : 'NO';
				break;

			case '1|0':
				$result = in_array($value, ['1', '0']) ? (int) $value : 0;
				break;

			case 'bool':
			case 'boolean':
				$result = boolval($value);

				break;

			case 'inputName':
				$result = preg_replace('/[^a-zA-Z0-9_\[\]]/', '_', $value);
				break;

			case 'unique':
				settype($value, 'array');
				$result = array_map('serialize', $value);
				$result = array_unique($result);
				$result = array_map('unserialize', $result);
				break;

			case 'basicHtml':
				$result = static::basicHtml($value);

				break;

			default:

				if (is_callable($type)) {
					$result = call_user_func_array($type, [$value]);
				} elseif (isset(static::$rules[$type])) {
					$result = call_user_func_array(static::$rules[$type], [$value]);
				} elseif (is_callable(static::class . '::' . $type)) {
					$result = call_user_func_array(static::class . '::' . $type, [$value]);
				} elseif (function_exists($type)) {
					$result = $type($value);
				} else {
					$result = $value;
				}

				break;
		}

		return $result;
	}

	public static function basicHtml($htmlString)
	{
		return strip_tags($htmlString, '<a><b><blockquote><code><del><dd><div><dl><dt><em><h1><h2><h3><h4><h5><h6><i><img><kbd><li><ol><p><pre><s><span><sup><sub><strong><ul><br><hr>');
	}

	public static function toPath($string)
	{
		$path = implode('/', array_map(static::class . '::toSlug', explode('/', trim(preg_replace(['/\/+|\\\\+/', '/\/+/'], '/', strtolower($string)), '/'))));

		return trim($path, '/\\\\.');
	}

	public static function toSlug($string)
	{
		$string = trim(preg_replace('/\s+/', '-', strtolower($string)), '-');
		$string = array_map(function ($str) {
			return static::stripMarks($str);
		}, explode('-', $string));

		$string = implode('-', $string);
		$string = preg_replace('/-+/', '-', $string);

		return trim($string, '/\\\\-');
	}

	public static function stripMarks($str)
	{
		// Lower
		$str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
		$str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
		$str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
		$str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
		$str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
		$str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
		$str = preg_replace('/(đ)/', 'd', $str);

		// Upper
		$str = preg_replace('/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/', 'A', $str);
		$str = preg_replace('/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/', 'E', $str);
		$str = preg_replace('/(Ì|Í|Ị|Ỉ|Ĩ)/', 'I', $str);
		$str = preg_replace('/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/', 'O', $str);
		$str = preg_replace('/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/', 'U', $str);
		$str = preg_replace('/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/', 'Y', $str);
		$str = preg_replace('/(Đ)/', 'D', $str);

		// Clean up
		$str = preg_replace('/[^a-zA-Z0-9-_]/', '', $str);

		return $str;
	}
}
