<?php

namespace MaiVu\Php\Form\Field;

class Number extends Text
{
	protected $inputType = 'number';
	protected $min = null;
	protected $max = null;

	public function load($config)
	{
		$min = $config['min'] ?? null;
		$max = $config['max'] ?? null;

		if ($min && !isset($config['rules']['MinLength:' . $min]))
		{
			$config['rules'][] = 'MinLength:' . $min;
		}

		if ($max && !isset($config['rules']['MaxLength:' . $max]))
		{
			$config['rules'][] = 'MaxLength:' . $max;
		}

		return parent::load($config);
	}

	protected function prepareInputAttribute(&$input)
	{
		if (is_numeric($this->min))
		{
			$input .= ' min="' . $this->min . '"';
		}

		if (is_numeric($this->max))
		{
			$input .= ' max="' . $this->max . '"';
		}
	}
}