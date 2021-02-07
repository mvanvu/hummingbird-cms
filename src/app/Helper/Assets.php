<?php

namespace App\Helper;

class Assets
{
	public static function core()
	{
		static $core = false;

		if (!$core)
		{
			$core = true;
			static::add('js/core.js');
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
						$file  = ROOT_URI . '/resources/public/' . Template::getTemplate()->id . '/' . $file;
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

	public static function inlineCss(string $content)
	{
		Service::assets()->addInlineCss($content);
	}

	public static function inlineJs(string $content)
	{
		Service::assets()->addInlineJs($content);
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
}