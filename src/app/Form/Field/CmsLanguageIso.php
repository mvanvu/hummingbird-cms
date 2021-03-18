<?php

namespace App\Form\Field;

use App\Helper\Utility;
use MaiVu\Php\Form\Field\Select;

class CmsLanguageIso extends Select
{
	public function getOptions()
	{
		$options = parent::getOptions();

		foreach (Utility::getIsoCodes() as $iso)
		{
			$options[] = [
				'value' => $iso,
				'text'  => $iso,
			];
		}

		return $options;
	}
}
