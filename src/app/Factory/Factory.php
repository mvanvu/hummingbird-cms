<?php

namespace App\Factory;

use App\Helper\Config;
use App\Helper\Constant;
use App\Helper\Event;
use App\Helper\Language;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\Utility;
use App\Loader;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Registry;
use Phalcon\Debug\Dump;

if (!function_exists('dd'))
{
	function dd()
	{
		@ob_clean();
		array_map(function ($x) {
			$string = (new Dump([], true))->variable($x);
			echo php_sapi_name() === 'cli' ? strip_tags($string) . PHP_EOL : $string;

		}, func_get_args());

		exit(0);
	}
}

class Factory
{
	/** @var Registry $config */
	protected static $config;

	/** @var WebApplication | ApiApplication $application */
	protected static $application;

	public static function getConfig()
	{
		return static::$config;
	}

	public static function getService($name, $parameters = null)
	{
		$di = static::getApplication()->getDI();

		switch ($name)
		{
			case 'assets':
			case 'url':
			case 'view':
			case 'db':
			case 'modelsMetadata':
			case 'modelsManager':
			case 'session':
			case 'flashSession':
			case 'sessionBag':
			case 'cookies':
			case 'security':
			case 'dispatcher':
			case 'router':
			case 'filter':
			case 'crypt':
			case 'request':
			case 'response':

				return $di->getShared($name, $parameters);
		}

		return $di->get($name, $parameters);
	}

	public static function getApplication()
	{
		if (!isset(static::$application))
		{
			require_once BASE_PATH . '/app/Loader.php';
			Loader::boot();

			// Initialise config data
			static::$config = static::loadConfig();

			// Initialise application
			static::$application = BaseApplication::getInstance();

			// Initialise languages
			Language::initialise();

			// Php form
			$formOptions = [
				'fieldNamespaces' => [Constant::NAMESPACE_FIELD],
				'ruleNamespaces'  => [Constant::NAMESPACE_RULE],
				'template'        => 'uikit-3',
				'messages'        => [
					'required' => 'required-field-msg',
					'invalid'  => 'invalid-field-value-msg',
				],
			];

			if (Language::isMultilingual())
			{
				$siteLangCode = Config::get('siteLanguage', 'en-GB');
				$siteLanguage = Language::get($siteLangCode);
				$iso          = array_flip(Utility::getIsoCodes());

				foreach (Language::getExistsLanguages() as $language)
				{
					if ($language->get('attributes.code') !== $siteLangCode)
					{
						$formOptions['languages'][$iso[$language->get('attributes.iso')]] = $language->get('attributes.code');
					}
				}

				ksort($formOptions['languages']);
				$formOptions['languages'] = array_merge(
					[
						$iso[$siteLanguage->get('attributes.iso')] => $siteLanguage->get('attributes.code'),
					],
					$formOptions['languages']
				);
			}

			Form::setOptions($formOptions);
			Form::setFieldTranslator(function (string $text, array $placeHolders = []) {
				return Text::_($text, $placeHolders);
			});

			Event::trigger('onBootApplication', [static::$application]);
		}

		return static::$application;
	}

	protected static function loadConfig()
	{
		if (!is_file(BASE_PATH . '/config.php'))
		{
			$installDistFile = PUBLIC_PATH . '/install.php-dist';
			$installFile     = PUBLIC_PATH . '/install.php';

			if (is_file($installDistFile) && !is_file($installFile))
			{
				rename($installDistFile, $installFile);
			}

			if (is_file($installFile))
			{
				header('location: ' . Uri::getHost() . '/install.php');
			}
			else
			{
				die('The config file not exists.');
			}
		}

		return Registry::create(BASE_PATH . '/config.php');
	}
}