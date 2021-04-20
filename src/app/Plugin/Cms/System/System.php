<?php

namespace App\Plugin\Cms;

use App\Factory\WebApplication;
use App\Helper\Form;
use App\Helper\Language;
use App\Helper\Menu;
use App\Helper\Service;
use App\Helper\State;
use App\Helper\Text;
use App\Helper\Uri;
use App\Plugin\Plugin;

class System extends Plugin
{
	public function onBootCms(WebApplication $app)
	{
		$request  = Service::request();
		$response = Service::response();

		if ($request->isPost() && !Form::checkToken())
		{
			if ($request->isAjax())
			{
				$response->setJsonContent(
					[
						'success' => false,
						'message' => Text::_('invalid-token-notice'),
					]
				)->send();

				exit(0);
			}

			Service::flashSession()->warning(Text::_('invalid-token-notice'));
			Uri::back();

			return false;
		}

		if ($request->isGet())
		{
			$uri       = preg_replace('/\?$/', '', Uri::fromServer(), 1);
			$cmsEditor = $request->get('cmsEditor');

			if (preg_match('/[^\/]\?/', $uri))
			{
				$uri = preg_replace('/\?/', '/?', $uri, 1);
				Uri::redirect($uri);

				return false;
			}

			if (in_array($cmsEditor, ['TinyMCE', 'CodeMirror']))
			{
				State::set('cms.editor.' . Uri::getActive()->toPath(), $cmsEditor);
			}
		}

		return $this->checkLanguage();
	}

	protected function checkLanguage()
	{
		$defaultLanguage = Language::getDefault();
		$activeLanguage  = Language::getActiveLanguage();
		$uri             = Uri::getActive();
		$vars            = $uri->getVars();
		$redirect        = false;

		if (!Language::isMultilingual())
		{
			if ($uri->getVar('client') === 'site' && $uri->hasVar('language'))
			{
				Uri::redirect($uri->delVar('language')->toString());
			}

			return true;
		}

		if (isset($vars['language']))
		{
			if ($defaultLanguage->get('locale.sef') === $vars['language'])
			{
				$uri->delVar('language');
				$redirect = true;
			}
		}
		elseif ($defaultLanguage->get('locale.code') !== $activeLanguage->get('locale.code'))
		{
			$uri->setVar('language', $activeLanguage->get('locale.code'));
			$redirect = true;
		}

		if ($redirect)
		{
			Uri::redirect($uri->toString());

			return false;
		}

		return true;
	}

	public function onRegisterMenus(&$menus)
	{
		$menus['link']         = $this->registerLink();
		$menus['header']       = $this->registerHeader();
		$menus['user-account'] = $this->registerUserAccount();
	}

	protected function registerLink()
	{
		return [
			'params' => [
				[
					'name'      => 'link',
					'type'      => 'Text',
					'label'     => 'URL',
					'class'     => 'uk-input',
					'required'  => true,
					'translate' => true,
					'filters'   => ['trim'],
				],
			],
			'route'  => function (Menu $menuItem) {
				return $menuItem->params->get('link');
			}
		];
	}

	protected function registerHeader()
	{
		return [
			'params' => [
				[
					'name'     => 'headerType',
					'type'     => 'Select',
					'label'    => 'menu-header-type',
					'class'    => 'uk-select',
					'required' => true,
					'options'  => [
						'header'  => 'menu-type-header',
						'divider' => 'menu-type-divider',
					],
					'rules'    => ['Options'],
				],
			],
		];
	}

	protected function registerUserAccount()
	{
		return [
			'route' => function (Menu $menuItem) {
				return Uri::route('user/account');
			},
		];
	}

	protected function forwardError($code = 404)
	{
		$dispatcher = Service::dispatcher();
		$dispatcher->setParams(
			[
				'code'    => $code,
				'title'   => Text::_($code . '-title'),
				'message' => Text::_($code . '-message'),
			]
		);

		$dispatcher->forward(
			[
				'controller' => 'error',
				'action'     => 'show',
			]
		);

		return false;
	}
}