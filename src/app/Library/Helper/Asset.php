<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use Phalcon\Assets\Manager as AssetsManager;
use MaiVu\Hummingbird\Lib\Factory;

class Asset extends AssetsManager
{
	protected static $code = [];
	protected static $assets = [
		'js'  => [],
		'css' => [],
	];

	public static function getFiles()
	{
		return self::$assets;
	}

	public static function core()
	{
		static $core = false;

		if (!$core)
		{
			$core = true;
			self::addFiles(
				[
					'core.js',
				]
			);
		}
	}

	public static function chosenCore()
	{
		static $chosen = false;

		if (!$chosen)
		{
			$chosen = true;
			self::addFiles(
				[
					'chosen/chosen.min.css',
					'chosen/chosen.jquery.min.js',
				]
			);
		}
	}

	public static function chosen($selector = '.select-chosen', $options = [])
	{
		self::chosenCore();
		$options = json_encode(array_merge([
			'rtl'                      => Text::_('direction', null, 'Locale') === 'rtl' ? true : false,
			'disable_search_threshold' => 10,
			'width'                    => '100%',
			'allow_single_deselect'    => true,
			'allow_custom_value'       => true,
		], $options));
		Factory::getService('assets')
			->addInlineJs(<<<JAVASCRIPT
$('{$selector}:not(.not-chosen)').addClass('has-chosen').chosen({$options}); 
JAVASCRIPT
			);
	}


	public static function tabState()
	{
		static $tabState = false;

		if (!$tabState)
		{
			$tabState = true;
			self::addFile('tab-state.js');
		}
	}

	public static function reactJsCore()
	{
		static $reactJs = false;

		if (!$reactJs)
		{
			$reactJs = true;
			$mode    = DEVELOPMENT_MODE ? 'development' : 'production.min';
			$assets  = Factory::getService('assets')
				->addJs('https://unpkg.com/react@16/umd/react.' . $mode . '.js', false, false, ['crossorigin' => ''])
				->addJs('https://unpkg.com/react-dom@16/umd/react-dom.' . $mode . '.js', false, false, ['crossorigin' => '']);

			if ($mode === 'development')
			{
				$assets->addJs('https://unpkg.com/babel-standalone@6/babel.min.js', false, false, null);
			}
		}
	}

	public static function tagEditorCore()
	{
		static $tagEditor = false;

		if (!$tagEditor)
		{
			$tagEditor = true;
			self::addFiles(
				[
					'tag-editor/jquery.tag-editor.min.css',
					'tag-editor/jquery.tag-editor.min.js',
				]
			);
		}
	}

	public static function tagEditor($selector = '.tag-area', $options = [])
	{
		self::tagEditorCore();
		$options = json_encode($options);
		Factory::getService('assets')->addInlineJs(<<<JAVASCRIPT
$('{$selector}').addClass('tag-area').tagEditor({$options}); 
JAVASCRIPT
		);
	}

	public static function jui()
	{
		static $jui = false;

		if (!$jui)
		{
			$jui = true;
			self::addFiles(
				[
					'js/jquery-ui/jquery-ui.js',
					'js/jquery-ui/jquery-ui.css',
				]
			);
		}
	}

	public static function calendar()
	{
		static $calendar = false;

		if (!$calendar)
		{
			self::jui();
			$calendar = true;
			$langCode = Language::getActiveCode();

			if (is_file(BASE_PATH . '/public/assets/js/jquery-ui/i18n/datepicker-' . $langCode . '.js'))
			{
				self::addFile('jquery-ui/i18n/datepicker-' . $langCode . '.js');
			}

			self::addFiles(
				[
					'calendar.css',
					'calendar.js',
				]
			);
		}
	}

	public static function addFiles(array $baseFiles, $basePath = PUBLIC_PATH . '/assets')
	{
		foreach ($baseFiles as $baseFile)
		{
			self::addFile($baseFile, $basePath);
		}
	}

	public static function addFile($baseFile, $basePath = PUBLIC_PATH . '/assets')
	{
		static $addedFiles = [];
		$key = $basePath . ':' . $baseFile;

		if (array_key_exists($key, $addedFiles))
		{
			return true;
		}

		$addedFiles[$key] = true;

		if (preg_match('/\.js$/', $baseFile))
		{
			$t = 'js';
		}
		else
		{
			$t = 'css';
		}

		$file = null;

		if (preg_match('/^https?:/', $baseFile))
		{
			$assets = Factory::getService('assets');

			return call_user_func_array([$assets, 'add' . ucfirst($t)], [$baseFile, false]);
		}

		if (is_file($baseFile))
		{
			$file = $baseFile;
		}
		elseif (is_file($basePath . '/' . $baseFile))
		{
			$file = $basePath . '/' . $baseFile;
		}
		elseif (is_file($basePath . '/' . $t . '/' . $baseFile))
		{
			$file = $basePath . '/' . $t . '/' . $baseFile;
		}

		if (!$file || in_array($file, self::$assets[$t]))
		{
			return false;
		}

		self::$assets[$t][] = $file;
	}

	public static function addCode($code)
	{
		self::$code[] = $code;
	}

	public static function getCode()
	{
		return implode(PHP_EOL, self::$code);
	}

	public static function inlineCss($css)
	{
		Factory::getService('assets')
			->addInlineCss($css);
	}

	public static function inlineJs($js)
	{
		Factory::getService('assets')
			->addInlineJs($js);
	}
}