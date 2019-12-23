<?php

namespace MaiVu\Hummingbird\Lib\Form\Rule;

use MaiVu\Hummingbird\Lib\Form\Field;

class Regex implements Rule
{
	public function validate(Field $field)
	{
		$value    = $field->getValue();
		$regex    = $field->get('regex', null);
		$required = $field->get('required', false);

		if (empty($regex) || (empty($value) && !$required))
		{
			return true;
		}

		return false !== preg_match('/' . $regex . '/', $value);
	}
}