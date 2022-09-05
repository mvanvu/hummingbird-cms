<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Email extends Rule
{
	public function validate(Field $field): bool
	{
		$required = $field->get('required');
		$value    = $field->getValue();

		return (!$required && empty($value)) || false !== filter_var($value, FILTER_VALIDATE_EMAIL);
	}
}