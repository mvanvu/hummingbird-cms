<?php

namespace MaiVu\Hummingbird\Lib\Form\Rule;

use MaiVu\Hummingbird\Lib\Form\Field;
use DateTime, Exception;

class Date implements Rule
{
	public function validate(Field $field)
	{
		$value    = $field->getValue();
		$required = $field->get('required', false);

		if (empty($value) && !$required)
		{
			return true;
		}

		try
		{
			new DateTime($value);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}
}