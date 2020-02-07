<?php

namespace MaiVu\Hummingbird\Widget\Menu;

use MaiVu\Hummingbird\Lib\Helper\Menu as MenuHelper;
use MaiVu\Hummingbird\Lib\Widget;

class Menu extends Widget
{
	public function getContent()
	{
		if ($items = MenuHelper::getMenuItems($this->widget->get('params.menuType')))
		{
			$renderer = $this->getRenderer();

			return $renderer->getPartial('Content/Navbar', ['items' => $items, 'renderer' => $renderer]);
		}

		return null;
	}
}