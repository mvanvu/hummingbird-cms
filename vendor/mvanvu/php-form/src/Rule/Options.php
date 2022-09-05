<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Options extends Rule
{
	public function validate(Field $field): bool
	{
		$optionValues = [];
		$value        = $field->getValue();
		$options      = $field->get('options', []);

		foreach ($options as $option)
		{
			if (isset($option['optgroup']))
			{
				foreach ($option['optgroup'] as $opt)
				{
					$optionValues[] = $opt['value'] ?? null;
				}
			}
			else
			{
				$optionValues[] = $option['value'] ?? null;
			}
		}

		if (null === $value && empty($optionValues))
		{
			return true;
		}

		if (is_array($value))
		{
			$diff = array_diff($value, $optionValues);

			return empty($diff);
		}

		return in_array((string) $value, $optionValues);
	}
}