<?php

namespace App\Form\Field;

use App\Helper\Language;
use MaiVu\Php\Form\Field\Select;

class CmsLanguage extends Select
{
	public function getOptions()
	{
		$options = [];

		foreach (Language::getExistsLanguages() as $langCode => $language)
		{
			$options[] = [
				'value' => $langCode,
				'text'  => $language->get('attributes.name'),
			];
		}

		return $options;
	}
}