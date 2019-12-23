<?php

namespace MaiVu\Hummingbird\Lib\Form\Rule;

use MaiVu\Hummingbird\Lib\Form\Field;

class Email implements Rule
{
	public function validate(Field $field)
	{
		$value    = $field->getValue();
		$required = $field->get('required', false);

		if (empty($value) && !$required)
		{
			return true;
		}

		return false !== filter_var($value, FILTER_VALIDATE_EMAIL);
	}
}