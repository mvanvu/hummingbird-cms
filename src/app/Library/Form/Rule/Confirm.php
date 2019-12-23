<?php

namespace MaiVu\Hummingbird\Lib\Form\Rule;

use MaiVu\Hummingbird\Lib\Form\Field;

class Confirm implements Rule
{
	public function validate(Field $field)
	{
		if ($confirmField = $field->getConfirmField())
		{
			return $field->getValue() === $confirmField->getValue();
		}

		return false;
	}
}