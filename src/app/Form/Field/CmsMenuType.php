<?php

namespace App\Form\Field;

use App\Helper\Menu;
use MaiVu\Php\Form\Field\Select;

class CmsMenuType extends Select
{
	public function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options = [];

			foreach (Menu::getMenuTypes() as $menuType)
			{
				$options[] = [
					'value' => $menuType->data,
					'text'  => $menuType->data,
				];
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
