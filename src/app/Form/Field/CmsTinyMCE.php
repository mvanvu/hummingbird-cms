<?php

namespace App\Form\Field;

use App\Helper\Editor;
use MaiVu\Php\Form\Field\TextArea;

class CmsTinyMCE extends TextArea
{
	public function toString()
	{
		$this->class = rtrim('js-editor-tinyMCE ' . $this->class);
		Editor::initTinyMCE();

		return parent::toString();
	}
}