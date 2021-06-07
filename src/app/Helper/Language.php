<?php

namespace App\Helper;

use App\Mvc\Model\Language as LangModel;
use MaiVu\Php\Registry;

class Language
{
	protected static $activeLanguage = null;
	protected static $languages = null;

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
			static::$languages = [
				'code' => [],
				'sef'  => [],
			];

			foreach (LangModel::find('state = \'P\'') as $language)
			{
				static::$languages['sef'][$language->sef]   = $language->code;
				static::$languages['code'][$language->code] = Registry::create(
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

			ksort(static::$languages['code']);
		}

		return static::$languages['code'];
	}

	public static function getActiveCode()
	{
		return static::getActive()->get('attributes.code');
	}

	public static function getActive(): Registry
	{
		if (null === static::$activeLanguage)
		{
			if (IS_CLI)
			{
				static::$activeLanguage = static::get(Config::get('administratorLanguage', 'en-GB'));
			}
			else
			{
				$activeLangCode = null;
				$client         = Uri::getClient();

				if (IS_API)
				{
					$request  = Service::request();
					$langCode = $request->getServer('HTTP_X_LANGUAGE_ISO_CODE');

					if ($langCode && Language::has($langCode))
					{
						$activeLangCode = $langCode;
					}

					if (($referer = $request->getHTTPReferer())
						&& ($refererUri = Uri::fromUrl($referer))
						&& $refererUri->isInternal()
					)
					{
						$client = $refererUri->getVar('client');

						if (!$activeLangCode
							&& ($langSef = $refererUri->getVar('language'))
							&& static::hasSef($langSef)
						)
						{
							$activeLangCode = static::getBySef($langSef)->get('attributes.code');
						}
					}
				}

				$confKey     = $client . 'Language';
				$cookieKey   = 'cms_' . $confKey;
				$defLangCode = Config::get($confKey, 'en-GB');

				if (!$activeLangCode && IS_CMS)
				{
					// Get current language SEF
					$uriLangSef = Uri::getActive()->getVar('language');

					// Get current language iso code by Cookie
					if (!($activeLangCode = Cookie::get($cookieKey, $defLangCode)) || !static::has($activeLangCode))
					{
						$activeLangCode = $defLangCode;
					}

					// If the language SEF is valid but it's not match with the current language iso code then
					// we will update the current language follows the current SEF (using by the Language Switcher widget)
					if (static::hasSef($uriLangSef) && $uriLangSef !== static::get($activeLangCode)->get('attributes.sef'))
					{
						$activeLangCode = static::getBySef($uriLangSef)->get('attributes.code');
					}

					if ($activeLangCode !== Cookie::get($cookieKey, $defLangCode))
					{
						// Update cookie language
						Cookie::set($cookieKey, $activeLangCode);
					}
				}

				static::$activeLanguage = static::get($activeLangCode);
			}
		}

		return static::$activeLanguage;
	}

	public static function get($langCode)
	{
		return static::has($langCode) ? static::$languages['code'][$langCode] : false;
	}

	public static function has($langCode): bool
	{
		return $langCode && array_key_exists($langCode, static::$languages['code']);
	}

	public static function hasSef($sef): bool
	{
		return $sef && array_key_exists($sef, static::$languages['sef']);
	}

	public static function getBySef($sef)
	{
		return static::hasSef($sef) ? static::get(static::$languages['sef'][$sef]) : false;
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

		if (isset(static::$languages['code'][$langCode]))
		{
			$strings = array_merge(static::$languages['code'][$langCode]->get('strings', []), Registry::parseData($data));
			static::$languages['code'][$langCode]->set('strings', $strings);
		}
	}

	/**
	 * @deprecated Use getActive instead
	 */

	public static function getActiveLanguage(): Registry
	{
		return static::getActive();
	}

	public static function _(string $string, array $placeholders = null): string
	{
		$active = static::getActive();

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
				$currentLanguage = static::getActive()->get('attributes.code');

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

		$defaultLangCode = Config::get($client . 'Language', 'en-GB');

		return static::get($defaultLangCode);
	}

	public static function getTranslations($langCode = null)
	{
		if (!$langCode)
		{
			$langCode = static::getActiveCode();
		}

		return isset(static::$languages['code'][$langCode]) ? static::$languages['code'][$langCode]->get('strings', []) : [];
	}
}
