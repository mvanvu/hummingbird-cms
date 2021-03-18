<?php

namespace App\Form\Field;

use App\Helper\Currency;
use MaiVu\Php\Form\Field\Select;

class CmsCurrencyList extends Select
{
	public function getOptions()
	{
		$options = [];

		foreach (Currency::load() as $currency)
		{
			$options[] = [
				'value' => $currency->code,
				'text'  => $currency->name,
			];
		}

		return $options;
	}
}