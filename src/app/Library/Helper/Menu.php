<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use MaiVu\Hummingbird\Lib\Mvc\Model\Config as ConfigModel;
use MaiVu\Php\Registry;

class Menu
{
	/** @var integer */
	public $id;

	/** @var integer */
	public $parentId;

	/** @var string */
	public $icon;

	/** @var string */
	public $title;

	/** @var string */
	public $type;

	/** @var string */
	public $menu;

	/** @var string */
	public $link;

	/** @var string */
	public $target;

	/** @var boolean */
	public $nofollow;

	/** @var boolean */
	public $active;

	/** @var Registry */
	public $params;

	/** @var string */
	public $rawData;

	/** @var array */
	protected static $menus = null;

	/** @var array */
	protected static $parentMenus = [];

	public static function getRegisteredMenus()
	{
		static $registeredMenus = null;

		if (null === $registeredMenus)
		{
			$registeredMenus = [];
			Event::trigger('registerMenus', [&$registeredMenus], ['System', 'Cms']);
		}

		return $registeredMenus;
	}

	public static function getMenus()
	{
		if (null === self::$menus)
		{
			$isSite          = Uri::isClient('site');
			$translate       = $isSite && Language::isMultilingual();
			$registeredMenus = self::getRegisteredMenus();
			$menus           = ConfigModel::find(
				[
					'conditions' => 'context = :context:',
					'bind'       => [
						'context' => 'cms.menu.item',
					],
					'order'      => 'ordering ASC',
				]
			);

			foreach ($menus as $menu)
			{
				$rawData  = $menu->data;
				$menuData = new Registry($rawData);

				if ($translate)
				{
					$translations = $menu->getTranslations();

					if (isset($translations['data']))
					{
						$menuData->merge($translations['data']);
					}
				}

				$menuType = $menuData->get('menu', '');
				$type     = $menuData->get('type', '');

				if (!isset($registeredMenus[$type]))
				{
					continue;
				}

				$menuConfig         = $registeredMenus[$type];
				$menuItem           = new Menu;
				$menuItem->id       = $menu->id;
				$menuItem->menu     = $menuType;
				$menuItem->type     = $type;
				$menuItem->icon     = $menuData->get('icon');
				$menuItem->title    = $menuData->get('title');
				$menuItem->target   = $menuData->get('target');
				$menuItem->nofollow = $menuData->get('nofollow') === 'Y';
				$menuItem->rawData  = $rawData;
				$menuItem->active   = false;
				$menuItem->params   = new Registry($menuData->get('params', []));

				if ($parentId = $menuData->get('parentId'))
				{
					self::$parentMenus[$parentId][] = $menuItem;
				}
				else
				{
					self::$menus[$menuType][$menuItem->id] = $menuItem;
				}

				if ($isSite && isset($menuConfig['route']))
				{
					$menuItem->link = call_user_func($menuConfig['route'], $menuItem);
					$currentUri     = Uri::getActive()->toString(false);

					if (empty($currentUri))
					{
						$currentUri = '/';
					}

					if ($menuItem->link === $currentUri)
					{
						$menuItem->active = true;
					}
				}
			}
		}

		return self::$menus;
	}

	public static function getMenuTypes()
	{
		static $menuTypes = null;

		if (null === $menuTypes)
		{
			$menuTypes = ConfigModel::find(
				[
					'conditions' => 'context = :context:',
					'bind'       => [
						'context' => 'cms.menu.type',
					],
					'order'      => 'data ASC',
				]
			);
		}

		return $menuTypes;
	}

	public static function getMenuItems($menuType)
	{
		$menus = self::getMenus();

		return isset($menus[$menuType]) ? $menus[$menuType] : [];
	}

	public function getChildren()
	{
		if ($this->id
			&& isset(self::$parentMenus[$this->id])
		)
		{
			return self::$parentMenus[$this->id];
		}

		return null;
	}

	public static function outputNestableList($menuType, array $menuItems = null)
	{
		$output = '<ol class="dd-list">';

		if (null === $menuItems)
		{
			$menuItems = self::getMenuItems($menuType);
		}

		foreach ($menuItems as $menuItem)
		{
			$children = $menuItem->getChildren();
			$dataMenu = htmlspecialchars(json_encode($menuItem));
			$title    = IconSvg::render('move', 18, 18) . ' ' . ($menuItem->icon ? IconSvg::render($menuItem->icon) . ' ' : '') . $menuItem->title;
			$output   .= '<li class="dd-item ' . ($children ? 'has-children' : 'no-children') . '" data-id="' . $menuItem->id . '" data-menu="' . $dataMenu . '">';
			$output   .= <<<HTML
<div class="dd-handle uk-box-shadow-hover-medium uk-position-relative">
	<a class="uk-link-reset">{$title}</a>
	<ul class="uk-iconnav uk-position-center-right uk-position-small uk-visible@s dd-nodrag">
		<li>
			<a class="edit" uk-icon="icon: pencil"></a>
		</li>
		<li>
			<a class="remove" uk-icon="icon: trash"></a>
		</li>
	</ul>
</div>
HTML;

			if ($children)
			{
				$output .= self::outputNestableList($menuType, $children);
			}

			$output .= '</li>';
		}

		$output .= '</ol>';

		return $output;
	}

	public static function renderMenu($menuType, $navType = 'Navbar')
	{
		return Widget::createWidget(
			'Menu',
			[
				'menuType' => $menuType,
			],
			true,
			'Raw'
		);
	}
}
