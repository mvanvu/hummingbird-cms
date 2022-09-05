<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Regex extends Rule
{
	public function validate(Field $field): bool
	{
		$regex = $this->params[0] ?? null;
		$value = $field->getValue();

		if (!$regex || (!is_string($value) && !is_numeric($value)))
		{
			return false;
		}

		return 1 === @preg_match('/' . $regex . '/', (string) $value);
	}
}