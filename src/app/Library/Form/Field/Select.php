<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Php\Registry;

class Select extends OptionAbstract
{
	/** @var array */
	protected $options = [];

	/** @var boolean */
	protected $multiple = false;

	public function getValue()
	{
		$value = parent::getValue();

		if ($this->multiple
			&& !is_array($value)
		)
		{
			$value = Registry::parseData($value);
		}

		return $value;
	}

	public function setOptions($options)
	{
		$this->options = Registry::parseData($options);

		return $this;
	}

	public function toString()
	{
		$input = '<select class="' . trim($this->class . ' uk-select') . '"'
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

		if ($this->multiple)
		{
			$input .= ' multiple';
		}

		$input      .= '>';
		$value      = $this->getValue();
		$valueArray = is_array($value) ? $value : [$value];

		foreach ($this->getOptions() as $optKey => $optValue)
		{
			if (is_array($optValue))
			{
				$input .= '<optgroup label="' . htmlspecialchars($optKey, ENT_COMPAT, 'UTF-8') . '">';

				foreach ($optValue as $k => $v)
				{
					$selected = in_array((string) $k, $valueArray) ? ' selected' : '';
					$input    .= '<option value="' . htmlspecialchars($k, ENT_COMPAT, 'UTF-8') . '"' . $selected . '>' . Text::_((string) $v) . '</option>';
				}

				$input .= '</optgroup>';
			}
			else
			{
				$selected = in_array((string) $optKey, $valueArray) ? ' selected' : '';
				$input    .= '<option value="' . htmlspecialchars($optKey, ENT_COMPAT, 'UTF-8') . '"' . $selected . '>' . Text::_((string) $optValue) . '</option>';
			}
		}

		return $input . '</select>';
	}
}