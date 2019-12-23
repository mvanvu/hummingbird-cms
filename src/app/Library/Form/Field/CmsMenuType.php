<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Helper\Menu;

class CmsMenuType extends Select
{
	public function getOptions()
	{
		$options = parent::getOptions();

		foreach (Menu::getMenuTypes() as $menuType)
		{
			$options[$menuType->data] = $menuType->data;
		}

		return $options;
	}
}
