<?php

namespace MaiVu\Php\Form\Rule;

use DateTime;
use Exception;
use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Date extends Rule
{
	public function validate(Field $field): bool
	{
		try
		{
			new DateTime($field->getValue());
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}
}