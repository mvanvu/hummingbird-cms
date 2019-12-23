<?php

namespace MaiVu\Hummingbird\Lib\Form\Rule;

use MaiVu\Hummingbird\Lib\Form\Field;

class Options implements Rule
{
	public function validate(Field $field)
	{
		$value    = $field->getValue();
		$required = $field->get('required', false);
		$options  = $field->get('options', []);

		if (empty($options) || (!$required && empty($value)))
		{
			return true;
		}

		$optionValues = [];

		foreach ($options as $optKey => $optValue)
		{
			if (is_array($optValue))
			{
				foreach ($optValue as $k => $v)
				{
					$optionValues[] = $k;
				}
			}
			else
			{
				$optionValues[] = $optKey;
			}
		}

		if (is_array($value))
		{
			$diff = array_diff($value, $optionValues);

			return empty($diff);
		}

		return in_array((string) $value, $optionValues);
	}
}