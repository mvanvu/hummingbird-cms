<?php

namespace App\Form\Rule;

use App\Mvc\Model\UcmItem;
use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class ValidUcmItem extends Rule
{
	public function validate(Field $field): bool
	{
		$value   = (int) $field->getValue();
		$isValid = false;

		if ($value > 0)
		{
			$isValid = false !== UcmItem::findFirst('state = \'P\' AND id = ' . $value);
		}

		return $isValid;
	}
}
