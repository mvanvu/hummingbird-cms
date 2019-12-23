<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Helper\Language;

class CmsLanguage extends Select
{
	public function getOptions()
	{
		$options = [];

		foreach (Language::getExistsLanguages() as $langCode => $language)
		{
			$options[$langCode] = $language->get('locale.title');
		}

		return $options;
	}
}