<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Form\Field;

class TextArea extends Field
{
	/** @var int */
	protected $rows = 5;

	/** @var int */
	protected $cols = 15;

	/** @var string */
	protected $hint = '';

	/** @var string */
	protected $autocomplete = '';

	public function toString()
	{
		$input = '<textarea class="' . $this->class . '"'
			. ' name="' . $this->getName() . '" id="' . $this->getId() . '"'
			. ' rows="' . (int) $this->rows . '" cols="' . (int) $this->cols . '"'
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

		$input .= '>' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';

		return $input;
	}
}