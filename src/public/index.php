<?php

use Phalcon\Debug\Dump;

define('BASE_PATH', dirname(__DIR__));

if (!function_exists('dd'))
{
	function dd()
	{
		@ob_clean();
		array_map(function ($x) {
			$string = (new Dump([], true))->variable($x);

			echo IS_CLI ? strip_tags($string) . PHP_EOL : $string;

		}, func_get_args());

		exit(0);
	}
}

try
{
	require_once BASE_PATH . '/app/Factory/Factory.php';
	App\Factory\Factory::getApplication()->execute();
}
catch (Throwable $e)
{
	echo $e->getMessage();
}