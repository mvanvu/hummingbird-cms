<?php

namespace App\Form\Field;


use App\Helper\Template;
use MaiVu\Php\Form\Field\Select;

class CmsTemplate extends Select
{
	protected $defaultFirst = false;

	public function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$options = [];

			foreach (Template::getTemplates() as $template)
			{
				$option = [
					'value' => $template->id,
					'text'  => $template->name,
				];

				if ($this->defaultFirst && $template->yes('isDefault'))
				{
					array_unshift($options, $option);
				}
				else
				{
					$options[] = $option;
				}
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
