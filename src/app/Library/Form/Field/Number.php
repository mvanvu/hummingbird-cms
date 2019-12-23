<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

class Number extends Text
{
	protected $inputType = 'number';
	protected $min = null;
	protected $max = null;

	protected function prepareInputAttribute(&$input)
	{
		if (is_numeric($this->min))
		{
			$input .= ' min="' . $this->min . '"';
		}

		if (is_numeric($this->max))
		{
			$input .= ' max="' . $this->max . '"';
		}
	}
}