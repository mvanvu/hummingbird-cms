<?php

namespace MaiVu\Php\Form\Field;

class Switcher extends Check
{
	public function toString()
	{
		return '<label class="switch-field">' . parent::toString() . '<span class="slider"></span></label>';
	}
}