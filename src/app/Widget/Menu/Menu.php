<?php

namespace App\Widget;

use App\Helper\Menu as MenuHelper;

class Menu extends Widget
{
	public function getContent(): ?string
	{
		if ($items = MenuHelper::getMenuItems($this->widget->get('params.menuType')))
		{
			$renderer = $this->getRenderer();

			return $renderer->getPartial(
				'Content/' . $this->widget->get('params.navType', 'Nav'),
				[
					'items'    => $items,
					'renderer' => $renderer,
				]
			);
		}

		return null;
	}
}