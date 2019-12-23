<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Form\Field;

class Check extends Field
{
	protected $checkboxValue = null;

	public function toString()
	{
		$input = '<input class="' . trim($this->class . ' uk-checkbox') . '"'
			. ' name="' . $this->getName() . '" type="checkbox" id="' . $this->getId() . '"'
			. ' value="' . htmlspecialchars($this->checkboxValue, ENT_COMPAT, 'UTF-8') . '"'
			. $this->getDataAttributesString();

		if ($this->required)
		{
			$input .= ' required';
		}

		if ($this->readonly)
		{
			$input .= ' readonly';
		}

		if ($this->checkboxValue === $this->value)
		{
			$input .= ' checked';
		}

		$input .= '/>';

		return $input;
	}
}