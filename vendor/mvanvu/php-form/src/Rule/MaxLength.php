<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class MaxLength extends Rule
{
	public function validate(Field $field): bool
	{
		$length = $this->params[0] ?? -1;

		if ($length)
		{
			$length = (int) $length;
			$value  = $field->getValue();

			if ($field->get('multiple'))
			{
				return is_array($value) && count($value) <= $length;
			}

			if (is_numeric($value))
			{
				return (int) $value <= $length;
			}

			return is_string($value) && strlen((string) $value) <= $length;
		}

		return false;
	}
}