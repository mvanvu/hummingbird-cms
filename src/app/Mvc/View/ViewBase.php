<?php

namespace App\Mvc\View;

use App\Factory\Factory;
use App\Helper\Volt as VoltHelper;
use Phalcon\Di;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;

class ViewBase extends View
{
	public static function getInstance(Di $di = null)
	{
		if (null === $di)
		{
			$di = Factory::getApplication()->getDI();
		}

		$view = new ViewBase;
		$view->setDI($di);
		$volt = new Volt($view, $di);
		$volt->setOptions(
			[
				'always' => DEVELOPMENT_MODE,
				'path'   => function ($templatePath) use ($volt) {

					if (!is_dir(CACHE_PATH . '/volt'))
					{
						mkdir(CACHE_PATH . '/volt', 0777, true);
					}

					return CACHE_PATH . '/volt/' . str_replace([APP_PATH . '/', '/'], ['', '_'], $templatePath) . '.php';
				},
			]
		);

		$compiler = $volt->getCompiler();
		$compiler->addExtension(new VoltHelper($compiler));
		$view->registerEngines(['.volt' => $volt]);
		$view->disableLevel(View::LEVEL_BEFORE_TEMPLATE);
		$view->disableLevel(View::LEVEL_LAYOUT);
		$view->disableLevel(View::LEVEL_AFTER_TEMPLATE);

		return $view;
	}
}