<?php

namespace MaiVu\Php\Form\Field\Base;

use MaiVu\Php\Form\Field;

class InputBase extends Field
{
	/** @var string */
	protected $inputType = 'text';

	/** @var string */
	protected $hint = null;

	/** @var string */
	protected $autocomplete = null;

	public function toString()
	{
		$value = $this->value;

		if (is_array($value) || is_object($value))
		{
			$value = json_encode($value);
		}

		$input = '<input' . ($this->class ? ' class="' . $this->class . '"' : '')
			. ' name="' . $this->getName() . '" type="' . $this->inputType . '" id="' . $this->getId() . '"'
			. ' value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"'
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

		if ($this->hint)
		{
			$input .= ' placeholder="' . htmlspecialchars($this->_($this->hint), ENT_COMPAT, 'UTF-8') . '"';
		}

		if ($this->autocomplete)
		{
			$input .= ' autocomplete="' . htmlspecialchars($this->autocomplete, ENT_COMPAT, 'UTF-8') . '"';
		}
		elseif ($this->inputType === 'password')
		{
			$input .= ' autocomplete="new-password"';
		}

		$this->prepareInputAttribute($input);

		$input .= '/>';

		return $input;
	}

	protected function prepareInputAttribute(&$input)
	{

	}
}