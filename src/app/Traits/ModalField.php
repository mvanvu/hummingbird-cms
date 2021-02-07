<?php


namespace App\Traits;

use MaiVu\Php\Filter;
use MaiVu\Php\Registry;

trait ModalField
{
	public function getOptions()
	{
		$options = $this->multiple ? parent::getOptions() : [];

		if ($array = $this->getParsedValue())
		{
			foreach ($array as $value)
			{
				$options[] = [
					'value' => $value,
					'text'  => $value,
				];
			}
		}

		return $options;
	}

	protected function getParsedValue()
	{
		$filters = $this->valueFilterCallBack ?? ['uint:array', 'unique'];
		
		return Filter::clean(Registry::parseData($this->getValue()), $filters);
	}
}