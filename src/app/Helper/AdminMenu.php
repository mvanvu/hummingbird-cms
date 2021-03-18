<?php

namespace App\Helper;

class AdminMenu
{
	protected $menus = [];

	public static function getInstance(): AdminMenu
	{
		static $instance = null;

		if (null === $instance)
		{
			$instance = new static;
			$user     = User::getActive();

			if ($user->authorise('media.manage'))
			{
				$instance->menus['media'] = [
					'title' => IconSvg::render('pictures') . ' ' . Text::_('media'),
					'url'   => Uri::route('media/index'),
				];
			}

			if ($user->authorise('tag.manage'))
			{
				$instance->menus['tag'] = [
					'title' => IconSvg::render('tag') . ' ' . Text::_('tag'),
					'url'   => Uri::route('tag/index'),
				];
			}

			if ($user->is('super'))
			{
				$instance->menus['system'] = [
					'title' => IconSvg::render('ios-settings') . ' ' . Text::_('system'),
					'items' => [
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
						[
							'title' => IconSvg::render('currency') . ' ' . Text::_('currencies'),
							'url'   => Uri::route('currency/index'),
						],
						[
							'title' => IconSvg::render('language') . ' ' . Text::_('languages'),
							'url'   => Uri::route('language/index'),
						],
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

				$instance->menus['user'] = [
					'title' => IconSvg::render('users') . ' ' . Text::_($userRoleText),
					'items' => $userRoleMenus,
				];
			}
		}

		return $instance;
	}

	public function getMenus(): array
	{
		return $this->menus;
	}

	public function addItem(string $parentName, array $item = []): AdminMenu
	{
		if (isset($this->menus[$parentName]))
		{
			$this->menus[$parentName]['items'][] = $item;
		}

		return $this;
	}

	public function addMenu(string $newName, array $newMenu, string $before = null): AdminMenu
	{
		if ($before && isset($this->menus[$before]))
		{
			$menus = [];

			foreach ($this->menus as $name => $menu)
			{
				if ($name === $before)
				{
					$menus[$newName] = $newMenu;
				}

				$menus[$name] = $menu;
			}

			$this->menus = $menus;
		}
		else
		{
			$this->menus = array_merge([$newName => $newMenu], $this->menus);
		}

		return $this;
	}
}