<?php

namespace App;

use Phalcon\Loader as PhalconLoader;

class Loader
{
	public static function boot()
	{
		if (!defined('CMS_VERSION'))
		{
			define('CMS_VERSION', '1.0-alpha');
			define('TMP_PATH', BASE_PATH . '/tmp');
			define('APP_PATH', BASE_PATH . '/app');
			define('PUBLIC_PATH', BASE_PATH . '/public');
			define('CACHE_PATH', BASE_PATH . '/cache');
			define('MVC_PATH', APP_PATH . '/Mvc');
			define('PLUGIN_PATH', APP_PATH . '/Plugin');
			define('WIDGET_PATH', APP_PATH . '/Widget');
			define('IS_CLI', php_sapi_name() === 'cli');
			define('IS_API', isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/hb/io/api/') === 0);
			define('IS_CMS', !IS_API && !IS_CLI);

			if (!IS_CLI)
			{
				$scriptDir = trim(dirname($_SERVER['SCRIPT_FILENAME']), '.');
				$baseUri   = preg_replace('#/?\?.*$#', '', trim($_SERVER['REQUEST_URI'], '/'));
				$rootUri   = preg_replace('#^' . preg_quote(BASE_PATH . '/public', '#') . '#', '', $scriptDir, 1);
				define('BASE_URI', $baseUri);
				define('ROOT_URI', $rootUri);
			}

			// Composer autoload
			require_once BASE_PATH . '/vendor/autoload.php';

			// CMS autoload
			(new PhalconLoader)->registerNamespaces(['App' => APP_PATH])->register();
		}
	}
}