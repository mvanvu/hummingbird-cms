<?php

namespace MaiVu\Hummingbird\Lib\Form\Rule;

use MaiVu\Hummingbird\Lib\Form\Field;

interface Rule
{
	public function validate(Field $field);
}
