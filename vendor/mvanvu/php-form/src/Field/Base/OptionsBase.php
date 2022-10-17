<?php

namespace MaiVu\Php\Form\Field\Base;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Registry;

abstract class OptionsBase extends Field
{
	protected $options = [];

	public function __construct($config, Form $form = null)
	{
		$config['options'] = $config['options'] ?? [];

		parent::__construct($config, $form);
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function setOptions($options)
	{
		$this->options = $this->parseOptions($options);

		return $this;
	}

	public function parseOptions($options)
	{
		$results = [];

		foreach (Registry::parseData($options) as $k => $option)
		{
			if (is_array($option))
			{
				$results[] = $option;
			}
			else
			{
				$results[] = [
					'value' => $k,
					'text'  => (string) $option,
				];
			}
		}

		return $results;
	}
}