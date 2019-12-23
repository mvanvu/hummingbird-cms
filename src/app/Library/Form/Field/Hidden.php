<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

class Hidden extends InputAbstract
{
	protected $inputType = 'hidden';
	protected $inputClass = 'uk-hidden';

	public function render()
	{
		return $this->toString();
	}
}