<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Form\Field\Base\InputBase;

class Hidden extends InputBase
{
	protected $inputType = 'hidden';

	public function render($options = [])
	{
		return $this->toString();
	}
}