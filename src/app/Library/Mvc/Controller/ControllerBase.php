<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Mvc\Controller;
use MaiVu\Hummingbird\Lib\Helper\Asset;
use MaiVu\Hummingbird\Lib\Helper\Config;
use MaiVu\Hummingbird\Lib\Helper\Event;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Helper\User;
use stdClass;

class ControllerBase extends Controller
{
	public function onConstruct()
	{
		$siteName = Config::get('siteName');

		if (Uri::isClient('site'))
		{
			$this->siteBase();
		}
		else
		{
			$format = $this->dispatcher->getParam('format');

			if ('raw' === $format)
			{
				$this->view->setMainView('Raw');
			}

			$this->tag->setTitle($siteName);
		}

		$this->view->setVars(
			[
				'siteName'  => $siteName,
				'cmsConfig' => Config::get(),
				'user'      => User::getInstance(),
			]
		);
	}

	protected function adminBase()
	{
		Asset::addFiles(
			[
				'admin.css',
				'core.js',
				'admin.js',
				'tab-state.js',
			]
		);
		Asset::chosen('.uk-select');
		$source              = new stdClass;
		$source->systemMenus = [];
		Event::trigger('registerSystemMenus', [$source], ['Cms']);
		$this->view->setVar('systemMenus', $source->systemMenus);
	}

	protected function siteBase()
	{
		Asset::addFile('core.js');
		$langCode    = Language::getActiveCode();
		$tplLangFile = TPL_SITE_PATH . '/Language/' . $langCode . '.php';

		if (is_file($tplLangFile)
			&& ($content = include $tplLangFile)
		)
		{
			Language::load($content, $langCode);
		}
	}
}
