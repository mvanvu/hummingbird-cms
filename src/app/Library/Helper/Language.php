<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use Phalcon\Http\Response\Cookies;
use MaiVu\Php\Registry;
use MaiVu\Hummingbird\Lib\Factory;

class Language
{
	protected static $languages = null;
	protected static $activeLanguage = null;
	protected static $languagesSef = [];

	/**
	 * @return Registry
	 */
	public static function getActiveLanguage()
	{
		if (null === self::$activeLanguage)
		{
			/** @var Cookies $cookies */
			$cookies         = Factory::getService('cookies');
			$vars            = Uri::extract();
			$key             = 'cms.' . $vars['client'] . '.language';
			$defaultLanguage = Config::get($vars['client'] . 'Language', 'en-GB');
			$expire          = time() + 15 * 86400;

			if (!$cookies->has($key)
				|| !self::has($cookies->get($key)->getValue())
			)
			{
				// Reset cookie language
				$cookies->set($key, $defaultLanguage, $expire);
			}

			$activeLanguage = self::get($cookies->get($key)->getValue());

			if (isset($vars['language'])
				&& $vars['language'] !== $activeLanguage->get('locale.sef')
				&& self::hasSef($vars['language']))
			{
				$activeLanguage = self::getBySef($vars['language']);

				// Update cookie language
				$cookies->set($key, $activeLanguage->get('locale.code'), $expire);
			}

			self::$activeLanguage = $activeLanguage;
		}

		return self::$activeLanguage;
	}

	public static function getExistsLanguages()
	{
		if (null === self::$languages)
		{
			self::$languages = [];

			foreach (FileSystem::scanDirs(APP_PATH . '/Language') as $langPath)
			{
				$localeFile = $langPath . '/Locale.php';

				if (is_file($localeFile))
				{
					$langCode = basename($langPath);
					$content  = include $localeFile;
					$language = new Registry;
					$language->set('locale', $content);
					self::$languagesSef[$content['sef']] = $langCode;
					self::$languages[$langCode]          = $language;
				}
			}
		}

		return self::$languages;
	}

	public static function initialise()
	{
		static $initialise = false;

		if (!$initialise)
		{
			$initialise = true;
			self::getExistsLanguages();
			$activeLanguage = self::getActiveCode();
			$contentFile    = APP_PATH . '/Language/' . $activeLanguage . '/' . $activeLanguage . '.php';

			if (is_file($contentFile)
				&& ($content = include $contentFile)
			)
			{
				self::load($content, $activeLanguage);
			}
		}
	}

	/**
	 * @param   null  $client
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

		return self::get($defaultLangCode);
	}

	public static function get($langCode)
	{
		return self::has($langCode) ? self::$languages[$langCode] : false;
	}

	public static function has($langCode)
	{
		return array_key_exists($langCode, self::$languages);
	}

	public static function hasSef($sef)
	{
		return array_key_exists($sef, self::$languagesSef);
	}

	public static function getBySef($sef)
	{
		return self::hasSef($sef) ? self::get(self::$languagesSef[$sef]) : false;
	}

	/**
	 * @var mixed  $data
	 * @var string $langCode
	 */

	public static function load($data, $langCode = null)
	{
		if (null === $langCode)
		{
			$langCode = self::getActiveCode();
		}

		if (isset(self::$languages[$langCode]))
		{
			$content = array_merge(self::$languages[$langCode]->get('content', []), Registry::parseData($data));
			self::$languages[$langCode]->set('content', $content);
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

		$translatedText = self::getActiveLanguage()->get($key, $string);

		if (is_array($placeholders) && $translatedText !== $string)
		{
			foreach ($placeholders as $key => $value)
			{
				$translatedText = str_replace('%' . $key . '%', $value, $translatedText);
			}
		}

		return $translatedText;
	}

	public static function isMultilingual()
	{
		return Config::get('multilingual', 'N') === 'Y';
	}

	public static function getLanguageQuery()
	{
		static $languageQuery = null;

		if (null === $languageQuery)
		{
			$languageQuery = '*';

			if (Uri::isClient('site') && self::isMultilingual())
			{
				$defaultLanguage = self::getDefault('site')->get('locale.code');
				$currentLanguage = self::getActiveLanguage()->get('locale.code');

				if ($defaultLanguage !== $currentLanguage)
				{
					$languageQuery = $currentLanguage;
				}
			}
		}

		return $languageQuery;
	}

	public static function getTranslations($langCode = null)
	{
		if (!$langCode)
		{
			$langCode = self::getActiveCode();
		}

		return isset(self::$languages[$langCode]) ? self::$languages[$langCode]->get('content', []) : [];
	}

	public static function getActiveCode()
	{
		return self::getActiveLanguage()->get('locale.code');
	}
}
