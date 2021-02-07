<?php

namespace App\Mvc\Controller;

use App\Helper\Assets;
use App\Helper\Config;
use App\Helper\Event;
use App\Helper\IconSvg;
use App\Helper\Language;
use App\Helper\Template;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\User;
use Phalcon\Mvc\Controller;
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
			$this->adminBase();
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
				'user'      => User::getActive(),
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
		Text::fetchJsData();
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

		$source              = new stdClass;
		$source->systemMenus = [];
		Event::trigger('onRegisterSystemMenus', [$source]);
		$user = User::getActive();

		if ($user->authorise('media.manage'))
		{
			$source->systemMenus[] = [
				'title' => IconSvg::render('pictures') . ' ' . Text::_('media'),
				'url'   => Uri::route('media/index'),
			];
		}

		if ($user->authorise('tag.manage'))
		{
			$source->systemMenus[] = [
				'title' => IconSvg::render('tag') . ' ' . Text::_('tag'),
				'url'   => Uri::route('tag/index'),
			];
		}

		if ($user->is('super'))
		{
			$source->systemMenus[IconSvg::render('ios-settings') . ' ' . Text::_('system')] = [
				[
					'title' => IconSvg::render('cog') . ' ' . Text::_('settings'),
					'url'   => Uri::route('config/index'),
				],
				[
					'title' => IconSvg::render('plug') . ' ' . Text::_('sys-plugins'),
					'url'   => Uri::route('plugin/index'),
				],
				[
					'title' => IconSvg::render('settings') . ' ' . Text::_('sys-widgets'),
					'url'   => Uri::route('widget/index'),
				],
				[
					'title' => IconSvg::render('menu') . ' ' . Text::_('menus'),
					'url'   => Uri::route('menu/index'),
				],
				[
					'title' => IconSvg::render('theatre') . ' ' . Text::_('templates'),
					'url'   => Uri::route('template/index'),
				],
			];
		}

		if ($user->authorise('user.manage'))
		{
			$userRoleMenus = [
				[
					'title' => IconSvg::render('users-o') . ' ' . Text::_('users'),
					'url'   => Uri::route('user/index'),
				],
			];

			if ($user->is('super'))
			{
				$userRoleText    = 'users-n-roles';
				$userRoleMenus[] = [
					'title' => IconSvg::render('lock-1') . ' ' . Text::_('user-roles'),
					'url'   => Uri::route('role/index'),
				];

				$userRoleMenus[] = [
					'title' => IconSvg::render('warning') . ' ' . Text::_('user-permissions'),
					'url'   => Uri::route('role/permit'),
				];
			}
			else
			{
				$userRoleText = 'users';
			}

			$source->systemMenus[IconSvg::render('users') . ' ' . Text::_($userRoleText)] = $userRoleMenus;
		}

		$this->view->setVar('systemMenus', $source->systemMenus);
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
