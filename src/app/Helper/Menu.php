<?php

namespace App\Helper;

use App\Mvc\Model\Config as ConfigModel;
use MaiVu\Php\Registry;

class Menu
{
	/**
	 * @var array
	 */
	protected static $menus = null;

	/**
	 * @var array
	 */
	protected static $parentMenus = [];
	/**
	 * @var array
	 *
	 */

	protected static $activeSiteMenus = [];
	/**
	 * @var integer
	 */
	public $id;

	/**
	 * @var integer
	 */
	public $parentId;

	/**
	 * @var integer
	 */
	public $templateId;

	/**
	 * @var string
	 */
	public $icon;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $menu;

	/**
	 * @var string
	 */
	public $link;

	/**
	 * @var string
	 */
	public $target;

	/**
	 * @var boolean
	 */
	public $nofollow;

	/**
	 * @var boolean
	 */
	public $active;

	/**
	 * @var Registry
	 */
	public $params;

	/**
	 * @var string
	 */
	public $rawData;

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

	public static function outputNestableList($menuType, array $menuItems = null)
	{
		$output = '<ol class="dd-list">';

		if (null === $menuItems)
		{
			$menuItems = static::getMenuItems($menuType);
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
				$output .= static::outputNestableList($menuType, $children);
			}

			$output .= '</li>';
		}

		$output .= '</ol>';

		return $output;
	}

	public static function getMenuItems($menuType): array
	{
		return static::getMenus()[$menuType] ?? [];
	}

	public static function getMenus(): array
	{
		if (null === static::$menus)
		{
			static::$menus   = [];
			$isSite          = Uri::isClient('site');
			$translate       = $isSite && Language::isMultilingual();
			$registeredMenus = static::getRegisteredMenus();
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

				if ($translate && $translations = $menu->getTranslations())
				{
					foreach (['templateId', 'icon', 'title', 'target', 'nofollow', 'params'] as $field)
					{
						if (!empty($translations['data'][$field]))
						{
							if ('params' === $field)
							{
								foreach ($translations['data']['params'] as $paramName => $paramValue)
								{
									if ('' !== $paramValue)
									{
										$menuData->set('params.' . $paramName, $paramValue);
									}
								}
							}
							elseif ('' !== $translations['data'][$field])
							{
								$menuData->set($field, $translations['data'][$field]);
							}
						}
					}
				}

				$menuItem             = new Menu;
				$menuType             = $menuData->get('menu', '');
				$menuItem->id         = $menu->id;
				$menuItem->menu       = $menuType;
				$menuItem->type       = $menuData->get('type', '');
				$menuItem->parentId   = $menuData->get('parentId', 0);
				$menuItem->templateId = $menuData->get('templateId', 0);
				$menuItem->icon       = $menuData->get('icon', '');
				$menuItem->title      = $menuData->get('title', '');
				$menuItem->target     = $menuData->get('target', '');
				$menuItem->nofollow   = $menuData->get('nofollow') === 'Y';
				$menuItem->rawData    = $rawData;
				$menuItem->active     = false;
				$menuItem->params     = new Registry($menuData->get('params', []));

				if ($menuItem->parentId)
				{
					static::$parentMenus[$menuItem->parentId][] = $menuItem;
				}
				else
				{
					static::$menus[$menuType][$menuItem->id] = $menuItem;
				}

				if ($isSite && isset($registeredMenus[$menuItem->type]['route']))
				{
					$route = $registeredMenus[$menuItem->type]['route'];

					if (is_string($route) && strpos($route, '::') === false)
					{
						$menuItem->link = Uri::route($route);
					}
					else
					{
						$menuItem->link = (string) call_user_func($route, $menuItem);
					}

					if ($menuItem->link
						&& $menuItem->link !== '#'
						&& $menuLink = Uri::fromUrl($menuItem->link)
					)
					{
						$menuItem->active = Uri::getActive(true) === $menuLink->toPath();

						if ($menuItem->active)
						{
							static::$activeSiteMenus[] = $menuItem;
						}
					}
					else
					{
						$menuItem->active = false;
					}
				}
			}
		}

		return static::$menus;
	}

	public static function getRegisteredMenus()
	{
		static $registeredMenus = null;

		if (null === $registeredMenus)
		{
			$registeredMenus = [];
			Event::trigger('onRegisterMenus', [&$registeredMenus]);
		}

		return $registeredMenus;
	}

	public static function getActiveSiteMenus()
	{
		// Initialise menus
		static::getMenus();

		return static::$activeSiteMenus;
	}

	public static function renderMenu($menuType, $navType = 'Navbar')
	{
		return Widget::createWidget(
			'Menu',
			[
				'menuType' => $menuType,
				'navType'  => $navType,
			],
			true,
			'Raw'
		);
	}

	public function getChildren()
	{
		return static::$parentMenus[$this->id] ?? [];
	}
}