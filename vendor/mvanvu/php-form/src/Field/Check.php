<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Form\Field\Base\InputBase;

class Check extends InputBase
{
	protected $inputType = 'checkbox';
	protected $checked = false;

	public function load($config)
	{
		if (isset($config['value']))
		{
			$this->value = (string) $config['value'];
			unset($config['value']);
		}

		return parent::load($config);
	}

	public function setValue($value)
	{
		$this->checked = ($value == $this->value);

		return $this;
	}

	public function getValue()
	{
		return $this->checked ? $this->value : $this->cleanValue(null);
	}

	public function applyFilters($value = null, $forceNull = false)
	{
		if (null === $value)
		{
			$forceNull = true;
		}

		return parent::applyFilters($value, $forceNull);
	}

	public function isChecked()
	{
		return $this->checked;
	}

	protected function prepareInputAttribute(&$input)
	{
		if ($this->checked)
		{
			$input .= ' checked';
		}
	}
}