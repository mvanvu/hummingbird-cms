<?php

namespace App\Helper;

use MaiVu\Php\Registry;

class Assets
{
	const POSITION_AFTER_HEAD = 1;

	const POSITION_BEFORE_BODY = 2;

	const POSITION_AFTER_BODY = 3;

	protected static $assets = [];

	public static function core(array $extendsAssets = [])
	{
		static $coreData = null;

		if (null === $coreData)
		{
			static::add(
				[
					'js/mini-query.js',
					'js/core.js',
				]
			);
			$currency = Currency::getActive();
			$registry = Registry::create($currency->params ?? []);
			$coreData = [
				'uri'      => [
					'isHome' => Uri::isHome(),
					'base'   => Uri::getBaseUriPrefix(),
					'root'   => ROOT_URI,
				],
				'currency' => [
					'code'          => $currency->code ?? 'USD',
					'symbol'        => $registry->get('symbol', '$'),
					'decimals'      => $registry->get('decimals', '2'),
					'separator'     => $registry->get('separator', ','),
					'point'         => $registry->get('point', '.'),
					'formatPattern' => $registry->get('format', '{symbol}{value}'),
				],
			];

			$inlineJS = 'cmsCore.uri = ' . json_encode($coreData['uri']) . ';';

			foreach ($coreData['currency'] as $k => $v)
			{
				$inlineJS .= PHP_EOL . 'cmsCore.currency.' . $k . ' = ' . json_encode($v) . ';';
			}

			static::inlineJs($inlineJS);
		}

		if ($extendsAssets)
		{
			static::add($extendsAssets);
		}
	}

	public static function add($asset, string $position = null)
	{
		static $files = [];

		if (is_array($asset))
		{
			foreach ($asset as $file)
			{
				static::add($file);
			}
		}
		elseif (!in_array($asset, $files))
		{
			$trimmed  = trim($asset, '/\\\\.');
			$files[]  = $trimmed;
			$type     = FileSystem::getExt($asset);
			$isSite   = Uri::isClient('site');
			$base     = ROOT_URI;
			$filePath = PUBLIC_PATH . '/' . $asset;

			if (in_array($type, ['css', 'js']))
			{
				if (preg_match('/^https?:|^\//', $asset))
				{
					$url = $asset;
				}
				else
				{
					$asset = $trimmed;

					if ($isSite)
					{
						$publicResource = TPL_SITE_PATH . '/public/' . $asset;

						if (is_file($publicResource))
						{
							$filePath = $publicResource;
							$base     = ROOT_URI . '/hb/io/public/' . Template::getTemplate()->id;
						}
					}

					if (!DEVELOPMENT_MODE && false === strpos($asset, '.min.'))
					{
						$dir   = dirname($filePath);
						$fName = basename($filePath, '.' . $type);

						if (is_file($dir . '/' . $fName . '.min.' . $type))
						{
							$asset = dirname($asset) . '/' . $fName . '.min.' . $type;
						}
					}

					$url = $base . '/' . $asset;
				}

				if (DEVELOPMENT_MODE)
				{
					$url .= '?' . time();
				}

				if ('js' === $type)
				{
					static::$assets[$position ?? Assets::POSITION_AFTER_BODY][] = '<script src="' . $url . '"></script>';
				}
				else
				{
					static::$assets[Assets::POSITION_AFTER_HEAD][] = '<link href="' . $url . '" rel="stylesheet" type="text/css"/>';
				}
			}
		}
	}

	public static function inlineJs(string $content, string $position = Assets::POSITION_AFTER_BODY)
	{
		static::$assets[$position][] = '<script>' . $content . '</script>';
	}

	public static function inlineCss(string $content)
	{
		static::$assets[Assets::POSITION_AFTER_HEAD][] = '<style>' . $content . '</style>';
	}

	public static function code(string $code, string $position = Assets::POSITION_AFTER_BODY)
	{
		static::$assets[$position][] = $code;
	}

	public static function jQueryCore()
	{
		static $jQuery = false;

		if (!$jQuery)
		{
			$jQuery = true;
			static::add('https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js');
		}
	}

	public static function tabState()
	{
		static $tabState = false;

		if (!$tabState)
		{
			$tabState = true;
			static::add('js/tab-state.js');
		}
	}

	public static function addFromPlugin($assets, string $group, string $name, string $position = null)
	{
		settype($assets, 'array');
		$isSite = Uri::isClient('site');

		foreach ($assets as $asset)
		{
			$asset    = trim($asset, '/\\\\.');
			$filePath = PLUGIN_PATH . '/' . $group . '/' . $name . '/' . $asset;
			$base     = ROOT_URI . '/hb/io/public/' . $group . '/' . $name;

			if ($isSite)
			{
				$publicResource = TPL_SITE_PATH . '/public/' . $group . '/' . $name . '/' . $asset;

				if (is_file($publicResource))
				{
					$filePath = $publicResource;
					$base     = ROOT_URI . '/hb/io/public/' . Template::getTemplate()->id . '/' . $group . '/' . $name;
				}
			}

			if (!DEVELOPMENT_MODE && false === strpos($asset, '.min.'))
			{
				$ext   = FileSystem::getExt($filePath);
				$dir   = dirname($filePath);
				$fName = basename($filePath, '.' . $ext);

				if (is_file($dir . '/' . $fName . '.min.' . $ext))
				{
					static::add($base . '/' . dirname($asset) . '/' . $fName . '.min.' . $ext, $position);
					continue;
				}
			}

			static::add($base . '/' . $asset, $position);
		}
	}

	public static function applyContent(string $content): string
	{
		Text::scripts();

		foreach (static::$assets as $position => $assets)
		{
			$code = implode(PHP_EOL, $assets);

			switch ($position)
			{
				case Assets::POSITION_AFTER_HEAD:
					$content = str_replace('<!--block:afterHead-->', $code, $content);
					break;

				case Assets::POSITION_BEFORE_BODY:
					$content = str_replace('<!--block:beforeBody-->', $code, $content);
					break;

				case Assets::POSITION_AFTER_BODY:
					$content = str_replace('<!--block:afterBody-->', $code, $content);
					break;
			}
		}

		return $content;
	}
}