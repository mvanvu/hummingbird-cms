<?php

namespace App\Mvc\Controller;

use App\Helper\AdminMenu;
use App\Helper\Assets;
use App\Helper\Config;
use App\Helper\Currency;
use App\Helper\Event;
use App\Helper\Language;
use App\Helper\Template;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\User;
use MaiVu\Php\Registry;
use Phalcon\Mvc\Controller;

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
			$this->adminBase();
			$format = $this->dispatcher->getParam('format');

			if ('raw' === $format)
			{
				$this->view->setMainView('Raw');
			}

			$this->tag->setTitle($siteName);
		}

		$currency = Currency::getActive();
		$registry = Registry::create($currency->params ?? []);

		$this->view->setVars(
			[
				'siteName'          => $siteName,
				'cmsConfig'         => Config::get(),
				'user'              => User::getActive(),
				'currencyCode'      => $currency->code ?? 'USD',
				'currencySymbol'    => $registry->get('symbol', '$'),
				'currencyDecimals'  => $registry->get('decimals', '2'),
				'currencySeparator' => $registry->get('separator', ','),
				'currencyPoint'     => $registry->get('point', '.'),
				'currencyFormat'    => $registry->get('format', '{symbol}{value}'),
			]
		);
	}

	protected function siteBase()
	{
		Assets::add(
			[
				'js/mini-query.js',
				'js/core.js',
			]
		);

		$langCode    = Language::getActiveCode();
		$tplLangFile = TPL_SITE_PATH . '/Language/' . $langCode . '.php';

		if (is_file($tplLangFile) && ($content = include $tplLangFile))
		{
			Language::load($content, $langCode);
		}

		$this->view->setVar('template', Template::getTemplate());
	}

	protected function adminBase()
	{
		Assets::add(
			[
				'css/admin.css',
				'css/php-form.css',
				'js/mini-query.js',
				'js/mini-query.choices.js',
				'js/mini-query.validate.js',
				'js/core.js',
				'js/admin.js',
				'js/tab-state.js',
				'js/php-form.js',
			]
		);

		$adminMenu = AdminMenu::getInstance();
		Event::trigger('onRegisterAdminMenus', [$adminMenu]);
		$this->view->setVar('adminMenus', $adminMenu->getMenus());
	}

	protected function notFound()
	{
		$this->dispatcher->forward(
			[
				'controller' => Uri::isClient('administrator') ? 'admin_error' : 'error',
				'action'     => 'show',
				'params'     => [
					'code'    => 404,
					'title'   => Text::_('404-title'),
					'message' => Text::_('404-message'),
				],
			]
		);

		return false;
	}
}
