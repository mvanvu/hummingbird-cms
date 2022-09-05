<?php

namespace MaiVu\Php\Form;

use MaiVu\Php\Registry;

abstract class Rule
{
	protected $params;

	public function __construct($params = [])
	{
		$this->params = new Registry($params);
	}

	abstract public function validate(Field $field): bool;
}
