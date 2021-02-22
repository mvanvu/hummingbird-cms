<?php

namespace App\Factory;

use App\Helper\Config;
use App\Helper\Constant;
use App\Helper\Language;
use App\Helper\Text;
use App\Loader;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Registry;

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

				foreach (Language::getExistsLanguages() as $language)
				{
					if ($language->get('locale.code') !== $siteLangCode)
					{
						$formOptions['languages'][$language->get('locale.code2')] = $language->get('locale.code');
					}
				}

				ksort($formOptions['languages']);
				$formOptions['languages'] = array_merge(
					[
						$siteLanguage->get('locale.code2') => $siteLanguage->get('locale.code'),
					],
					$formOptions['languages']
				);
			}

			Form::setOptions($formOptions);
			Form::setFieldTranslator(function (string $text, array $placeHolders = []) {
				return Text::_($text, $placeHolders);
			});
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
				$protocol = 'http';

				if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
				{
					$protocol .= 's';
				}

				header('location: ' . $protocol . '://' . $_SERVER['HTTP_HOST'] . '/install.php');
			}
			else
			{
				die('The config file not exists.');
			}
		}

		return Registry::create(BASE_PATH . '/config.php');
	}
}