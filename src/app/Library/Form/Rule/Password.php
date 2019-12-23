<?php

namespace MaiVu\Hummingbird\Lib\Form\Rule;

use MaiVu\Hummingbird\Lib\Form\Field;
use MaiVu\Hummingbird\Lib\Helper\User;

class Password implements Rule
{
	public function validate(Field $field)
	{
		$value    = $field->getValue();
		$required = $field->get('required', false);

		if (empty($value) && !$required)
		{
			return true;
		}

		return true === User::validatePassword($value);
	}
}