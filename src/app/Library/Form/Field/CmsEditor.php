<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Helper\Editor;

class CmsEditor extends TextArea
{
	public function toString()
	{
		$this->class = rtrim('js-editor-tinyMCE ' . $this->class);
		Editor::initTinyMCE();

		return parent::toString();
	}
}