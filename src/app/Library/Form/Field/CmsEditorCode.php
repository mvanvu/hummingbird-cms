<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Helper\Editor;
use MaiVu\Hummingbird\Lib\Helper\Text;

class CmsEditorCode extends TextArea
{
	public function toString()
	{
		$this->class = rtrim('js-editor-codemirror ' . $this->class);
		Editor::initCodeMirror();

		return '<p class="uk-text-meta">' . Text::_('f10-toggle-full-screen-desc') . '</p>' . parent::toString();
	}
}