<?php

namespace App\Helper;

use MaiVu\Php\Registry;

class Language
{
	protected static $languages = null;
	protected static $activeLanguage = null;
	protected static $languagesSef = [];

	public static function initialise()
	{
		static $initialised = false;

		if (!$initialised)
		{
			static::getExistsLanguages();
			$initialised    = true;
			$activeLanguage = static::getActiveCode();
			$contentFile    = APP_PATH . '/Language/' . $activeLanguage . '/' . $activeLanguage . '.php';

			if (is_file($contentFile) && ($content = include $contentFile))
			{
				static::load($content, $activeLanguage);
			}
		}
	}

	public static function getExistsLanguages()
	{
		if (null === static::$languages)
		{
			static::$languages = [];

			foreach (FileSystem::scanDirs(APP_PATH . '/Language') as $langPath)
			{
				$localeFile = $langPath . '/Locale.php';

				if (is_file($localeFile))
				{
					$langCode = basename($langPath);
					$content  = include $localeFile;
					$language = new Registry;
					$language->set('locale', $content);
					static::$languagesSef[$content['sef']] = $langCode;
					static::$languages[$langCode]          = $language;
				}

				ksort(static::$languages);
			}
		}

		return static::$languages;
	}

	public static function getActiveCode()
	{
		return static::getActiveLanguage()->get('locale.code');
	}

	/**
	 * @return Registry
	 */
	public static function getActiveLanguage()
	{
		if (null === static::$activeLanguage)
		{
			if (IS_CLI)
			{
				static::$activeLanguage = static::get(Config::get('administratorLanguage', 'en-GB'));
			}
			else
			{
				$vars        = Uri::extract();
				$confKey     = $vars['client'] . 'Language';
				$key         = 'cms_' . $confKey;
				$defLangCode = Config::get($confKey, 'en-GB');

				if (!($activeLangCode = Cookie::get($key)))
				{
					// Reset cookie language
					$activeLangCode = $defLangCode;
					Cookie::set($key, $activeLangCode);
				}

				if (isset($vars['language'])
					&& static::hasSef($vars['language'])
					&& $vars['language'] !== static::get($activeLangCode)->get('locale.sef')
				)
				{
					// Update cookie language
					$activeLangCode = static::getBySef($vars['language'])->get('locale.code');
					Cookie::set($key, $activeLangCode);
				}
				elseif (!isset($vars['language']) && $activeLangCode !== $defLangCode)
				{
					$activeLangCode = $defLangCode;
					Cookie::set($key, $activeLangCode);
				}

				static::$activeLanguage = static::get($activeLangCode);
			}
		}

		return static::$activeLanguage;
	}

	public static function get($langCode)
	{
		return static::has($langCode) ? static::$languages[$langCode] : false;
	}

	public static function has($langCode)
	{
		return array_key_exists($langCode, static::$languages);
	}

	public static function hasSef($sef)
	{
		return array_key_exists($sef, static::$languagesSef);
	}

	public static function getBySef($sef)
	{
		return static::hasSef($sef) ? static::get(static::$languagesSef[$sef]) : false;
	}

	/**
	 * @var mixed       $data
	 * @var string|null $langCode
	 */

	public static function load($data, $langCode = null)
	{
		if (null === $langCode)
		{
			$langCode = static::getActiveCode();
		}

		if (isset(static::$languages[$langCode]))
		{
			$content = array_merge(static::$languages[$langCode]->get('content', []), Registry::parseData($data));
			static::$languages[$langCode]->set('content', $content);
		}
	}

	public static function _($string, $placeholders = null)
	{
		if (strpos($string, 'locale.') === 0)
		{
			$key    = $string;
			$string = str_replace('locale.', '', $string);
		}
		else
		{
			$key = 'content.' . $string;
		}

		$translatedText = static::getActiveLanguage()->get($key, $string);

		if (is_array($placeholders) && $translatedText !== $string)
		{
			foreach ($placeholders as $key => $value)
			{
				$translatedText = str_replace('%' . $key . '%', $value, $translatedText);
			}
		}

		return $translatedText;
	}

	public static function getLanguageQuery()
	{
		static $languageQuery = null;

		if (null === $languageQuery)
		{
			$languageQuery = '*';

			if (Uri::isClient('site') && static::isMultilingual())
			{
				$defaultLanguage = static::getDefault('site')->get('locale.code');
				$currentLanguage = static::getActiveLanguage()->get('locale.code');

				if ($defaultLanguage !== $currentLanguage)
				{
					$languageQuery = $currentLanguage;
				}
			}
		}

		return $languageQuery;
	}

	public static function isMultilingual()
	{
		return Config::get('multilingual', 'N') === 'Y';
	}

	/**
	 * @param null $client
	 *
	 * @return boolean|Registry
	 */

	public static function getDefault($client = null)
	{
		if (!in_array($client, ['site', 'administrator']))
		{
			$client = Uri::isClient('site') ? 'site' : 'administrator';
		}

		$defaultLangCode = Config::get($client . 'Language');

		return static::get($defaultLangCode);
	}

	public static function getTranslations($langCode = null)
	{
		if (!$langCode)
		{
			$langCode = static::getActiveCode();
		}

		return isset(static::$languages[$langCode]) ? static::$languages[$langCode]->get('content', []) : [];
	}
}
