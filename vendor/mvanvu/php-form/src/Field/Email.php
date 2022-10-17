<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Form\Form;

class Email extends Text
{
	protected $inputType = 'email';

	public function __construct($config, Form $form = null)
	{
		$config['rules'] = array_merge(['Email'], ($config['rules'] ?? []));

		parent::__construct($config, $form);
	}
}