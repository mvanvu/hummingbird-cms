<?php

use App\Factory\Factory;
use Phalcon\Debug\Dump;

define('BASE_PATH', __DIR__);

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

require_once BASE_PATH . '/app/Factory/Factory.php';

return Factory::getApplication();