<?php

namespace App\Helper;

use MaiVu\Php\Registry;

class Assets
{
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

	public static function add($asset)
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
			$files[] = $asset;
			$type    = FileSystem::splitExt($asset);

			if (in_array($type, ['css', 'js']))
			{
				$callback = 'add' . ucfirst($type);
				$local    = preg_match('/^https?:|^\//', $asset) ? false : true;

				if (!DEVELOPMENT_MODE && is_file(PUBLIC_PATH . '/' . $asset . '.min.' . $type))
				{
					$file = $asset . '.min.' . $type;
				}
				else
				{
					$file = $asset . '.' . $type;
				}

				if (Uri::isClient('site'))
				{
					$publicResource = TPL_SITE_PATH . '/public/' . $file;

					if (is_file($publicResource))
					{
						$file  = ROOT_URI . '/hb/io/public/' . Template::getTemplate()->id . '/' . $file;
						$local = false;
					}
				}

				if (DEVELOPMENT_MODE)
				{
					$file .= '?' . time();
				}

				Service::assets()->{$callback}($file, $local);
			}
		}
	}

	public static function inlineJs(string $content)
	{
		Service::assets()->addInlineJs($content);
	}

	public static function inlineCss(string $content)
	{
		Service::assets()->addInlineCss($content);
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

	public static function addFromPlugin($assets, string $group, string $name)
	{
		settype($assets, 'array');
		$prefix = ROOT_URI . '/hb/io/public/' . $group . '/' . $name;

		foreach ($assets as &$asset)
		{
			$asset = trim($asset, '/\\\\.');

			if (!DEVELOPMENT_MODE && false === strpos($asset, '.min.'))
			{
				$ext   = FileSystem::getExt($asset);
				$dir   = dirname($asset);
				$fName = basename($asset, '.' . $ext);

				if (is_file(PLUGIN_PATH . '/' . $group . '/' . $name . '/public/' . $dir . '/' . $fName . '.min.' . $ext))
				{
					$asset = $dir . '/' . $fName . '.min.' . $ext;
				}
			}

			if (strpos($asset, $prefix) !== 0)
			{
				$asset = $prefix . '/' . $asset;
			}
		}

		static::add($assets);
	}
}