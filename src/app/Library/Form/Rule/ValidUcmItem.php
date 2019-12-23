<?php

namespace MaiVu\Hummingbird\Lib\Form\Rule;

use MaiVu\Hummingbird\Lib\Form\Field;
use MaiVu\Hummingbird\Lib\Mvc\Model\UcmItem;

class ValidUcmItem implements Rule
{
	public function validate(Field $field)
	{
		$value   = (int) $field->getValue();
		$isValid = false;

		if ($value > 0)
		{
			$isValid = UcmItem::findFirst('state = \'P\' AND id = ' . $value) ? true : false;
		}

		return $isValid;
	}
}
