<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Mvc\Model\Translation;
use MaiVu\Hummingbird\Lib\Form\Field;
use MaiVu\Php\Registry;

abstract class OptionAbstract extends Field
{
	/** @var array */
	protected $options = [];

	public function setOptions($options)
	{
		$this->options = Registry::parseData($options);

		return $this;
	}

	public function getOptions()
	{
		if ('*' !== $this->language
			&& $this->ucmFieldId > 0
		)
		{
			static $optionsParams = null;

			if (null === $optionsParams)
			{
				$optionsParams = [];
				$translations  = Translation::find(
					[
						'conditions' => 'translationId LIKE :translationId:',
						'bind'       => [
							'translationId' => '%.ucm_fields.id=%.params',
						],
					]
				);

				if ($translations->count())
				{
					foreach ($translations as $translation)
					{
						$parts    = explode('.', $translation->translationId);
						$language = $parts[0];
						$fieldId  = str_replace('id=', '', $parts[2]);

						$optionsParams[$fieldId][$language] = new Registry($translation->translatedValue);
					}
				}
			}

			if (isset($optionsParams[$this->ucmFieldId][$this->language])
				&& ($options = $optionsParams[$this->ucmFieldId][$this->language]->get('options', []))
			)
			{
				$this->options = Registry::parseData($options);
			}
		}

		return $this->options;
	}
}