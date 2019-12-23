<?php

namespace MaiVu\Hummingbird\Plugin\System\Cms;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\Menu;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Plugin as CmsPlugin;
use MaiVu\Hummingbird\Lib\Mvc\Model\Post;
use MaiVu\Hummingbird\Lib\Mvc\Model\PostCategory;
use MaiVu\Hummingbird\Lib\Factory;

class Cms extends CmsPlugin
{
	protected function checkLanguage()
	{
		if (!Language::isMultilingual())
		{
			return true;
		}

		$defaultLanguage = Language::getDefault();
		$activeLanguage  = Language::getActiveLanguage();
		$uri             = Uri::getActive();
		$vars            = $uri->getVars();
		$redirect        = false;

		if (isset($vars['language']))
		{
			if ($defaultLanguage->get('locale.sef') === $vars['language'])
			{
				$uri->setVar('language', null);
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
			return Factory::getService('response')->redirect($uri->toString(), true);
		}

		return true;
	}

	protected function forwardError404()
	{
		$dispatcher = Factory::getService('dispatcher');
		$dispatcher->setParams(
			[
				'code'    => '403',
				'title'   => Text::_('403-title'),
				'message' => Text::_('403-message'),
			]
		);

		return $dispatcher->forward(
			[
				'controller' => 'error',
				'action'     => 'show',
			]
		);
	}

	protected function checkPermission()
	{
		$user           = User::getInstance();
		$dispatcher     = Factory::getService('dispatcher');
		$controllerName = $dispatcher->getControllerName();
		$actionName     = $dispatcher->getActionName();

		if (in_array($controllerName, ['error', 'admin_error'])
			|| (in_array($controllerName, ['user', 'admin_user'])
				&& in_array($actionName, ['login', 'logout', 'account']))
		)
		{
			return true;
		}

		if (Uri::isClient('administrator'))
		{
			if ($user->isGuest())
			{
				return 'forward';
			}

			$systemResources = [
				'admin_config',
				'admin_widget',
				'admin_plugin',
			];

			$isSystemResource = in_array($controllerName, $systemResources);

			if (($isSystemResource && !$user->access('super'))
				|| !$user->access('manager')
			)
			{
				return false;
			}
		}

		return true;
	}

	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
	{
		if (Factory::getService('request')->isGet())
		{
			$uri = preg_replace('/\?$/', '', Uri::getActive(true), 1);

			if (preg_match('/[^\/]\?/', $uri))
			{
				$uri = preg_replace('/\?/', '/?', $uri, 1);

				return Factory::getService('response')->redirect($uri);
			}
		}

		$permission = $this->checkPermission();

		if (false === $permission)
		{
			return $this->forwardError404();
		}

		if ('forward' === $permission)
		{
			return Factory::getService('dispatcher')->forward(
				[
					'controller' => 'admin_user',
					'action'     => 'login',
				]
			);
		}

		return $this->checkLanguage();
	}

	protected function registerPost()
	{
		return [
			'params' => [
				[
					'name'     => 'postId',
					'type'     => 'CmsModalUcmItem',
					'context'  => 'post',
					'required' => true,
					'filters'  => ['uint'],
					'rules'    => ['ValidUcmItem'],
					'messages' => [
						'ValidUcmItem' => 'post-not-found',
					],
				],
			],
			'route'  => function (Menu $menuItem) {
				$postId = $menuItem->params->get('postId', 0);

				if ($post = Post::findFirst('id = ' . (int) $postId))
				{
					return $post->getLink();
				}

				return null;
			}
		];
	}

	protected function registerPostCategory()
	{
		return [
			'params' => [
				[
					'name'     => 'categoryId',
					'type'     => 'CmsModalUcmItem',
					'context'  => 'post-category',
					'filters'  => ['uint'],
					'rules'    => ['ValidUcmItem'],
					'messages' => [
						'ValidUcmItem' => 'category-not-found',
					],
				],
			],
			'route'  => function (Menu $menuItem) {
				$categoryId = $menuItem->params->get('categoryId', 0);

				if ($category = PostCategory::findFirst('id = ' . (int) $categoryId))
				{
					return $category->getLink();
				}

				return null;
			}
		];
	}

	protected function registerLink()
	{
		return [
			'params' => [
				[
					'name'      => 'link',
					'type'      => 'Text',
					'label'     => 'URL',
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
					'class'    => 'uk-form-small',
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

	public function registerMenus(&$menus)
	{
		$menus['post']          = $this->registerPost();
		$menus['post-category'] = $this->registerPostCategory();
		$menus['link']          = $this->registerLink();
		$menus['header']        = $this->registerHeader();
		$menus['user-account']  = $this->registerUserAccount();
	}

	public function onRequestGetLoadStrings($requestData, &$responseData)
	{
		$responseData['data'] = Language::getTranslations();
	}
}