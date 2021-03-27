<?php

namespace App\Helper;

use App\Mvc\Model\Language as LangModel;
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

			if (!static::has($activeLanguage))
			{
				$activeLanguage = 'en-GB';
			}

			$stringsFile = APP_PATH . '/Language/' . $activeLanguage . '.php';

			if (is_file($stringsFile) && ($content = include $stringsFile))
			{
				static::load($stringsFile, $activeLanguage);
			}
		}
	}

	public static function getExistsLanguages()
	{
		if (null === static::$languages)
		{
			static::$languages = [];

			foreach (LangModel::find('state = \'P\'') as $language)
			{
				static::$languagesSef[$language->sef] = $language->code;
				static::$languages[$language->code]   = Registry::create(
					[
						'strings'    => [],
						'attributes' => [
							'id'        => $language->id,
							'name'      => $language->name,
							'code'      => $language->code,
							'iso'       => $language->iso,
							'sef'       => $language->sef,
							'direction' => $language->direction,
							'params'    => Registry::create($language->params)->toArray(),
							'emoji'     => Utility::getCountryFlagEmoji(array_flip(Utility::getIsoCodes())[$language->iso] ?? $language->iso)
						],
					]
				);
			}

			ksort(static::$languages);
		}

		return static::$languages;
	}

	public static function getActiveCode()
	{
		return static::getActiveLanguage()->get('attributes.code');
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
					&& $vars['language'] !== static::get($activeLangCode)->get('attributes.sef')
				)
				{
					// Update cookie language
					$activeLangCode = static::getBySef($vars['language'])->get('attributes.code');
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

	public static function load($data, string $langCode = null)
	{
		if (null === $langCode)
		{
			$langCode = static::getActiveCode();
		}

		if (isset(static::$languages[$langCode]))
		{
			$strings = array_merge(static::$languages[$langCode]->get('strings', []), Registry::parseData($data));
			static::$languages[$langCode]->set('strings', $strings);
		}
	}

	public static function _(string $string, array $placeholders = null): string
	{
		$active = static::getActiveLanguage();

		if (strpos($string, '@') === 0)
		{
			return $active->get('attributes.' . substr($string, 1), $string);
		}

		$translatedText = $active->get('strings.' . $string, $string);

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
				$defaultLanguage = static::getDefault('site')->get('attributes.code');
				$currentLanguage = static::getActiveLanguage()->get('attributes.code');

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

		return isset(static::$languages[$langCode]) ? static::$languages[$langCode]->get('strings', []) : [];
	}
}
