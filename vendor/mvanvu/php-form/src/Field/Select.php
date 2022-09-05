<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Form\Field\Base\OptionsBase;
use MaiVu\Php\Registry;

class Select extends OptionsBase
{
	/** @var array */
	protected $options = [];

	/** @var boolean */
	protected $multiple = false;

	public function toString()
	{
		$input = '<select' . ($this->class ? ' class="' . $this->class . '"' : '')
			. ' name="' . $this->getName() . ($this->multiple ? '[]' : '') . '" id="' . $this->getId() . '"'
			. $this->getDataAttributesString();

		if ($this->required)
		{
			$input .= ' required';
		}

		if ($this->readonly)
		{
			$input .= ' readonly';
		}

		if ($this->disabled)
		{
			$input .= ' disabled';
		}

		if ($this->multiple)
		{
			$input .= ' multiple';
		}

		$input      .= '>';
		$value      = $this->getValue();
		$valueArray = is_array($value) ? $value : [$value];

		foreach ($this->getOptions() as $options)
		{
			if (isset($options['optgroup']))
			{
				$input .= '<optgroup label="' . $this->renderValue($options['label'] ?? 'No Label') . '">';

				foreach ($options['optgroup'] as $opt)
				{
					$optValue = (string) ($opt['value'] ?? '');
					$optText  = (string) ($opt['text'] ?? '');
					$attr     = in_array($optValue, $valueArray) ? ' selected' : '';

					if (!empty($opt['disabled']))
					{
						$attr .= ' disabled';
					}

					$input .= '<option value="' . $this->renderValue($optValue) . '"' . $attr . '>' . $this->renderText($optText) . '</option>';
				}

				$input .= '</optgroup>';
			}
			else
			{
				$optValue = (string) ($options['value'] ?? '');
				$attr     = in_array($optValue, $valueArray) ? ' selected' : '';

				if (!empty($options['disabled']))
				{
					$attr .= ' disabled';
				}

				$input .= '<option value="' . $this->renderValue($optValue) . '"' . $attr . '>' . $this->renderText(($options['text'] ?? '')) . '</option>';
			}
		}

		return $input . '</select>';
	}

	public function getValue()
	{
		$value = parent::getValue();

		if ($this->multiple && !is_array($value))
		{
			$value = Registry::parseData($value);
		}

		return $value;
	}
}