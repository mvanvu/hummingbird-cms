<?php

namespace App\Form\Field;

use App\Helper\Editor;
use App\Helper\Text;
use MaiVu\Php\Form\Field\TextArea;

class CmsCodeMirror extends TextArea
{
	protected $showHint = true;

	public function toString()
	{
		$this->class = rtrim('js-editor-codemirror ' . $this->class);
		Editor::initCodeMirror();
		$editor = parent::toString();

		if ($this->showHint)
		{
			$editor = '<p class="uk-text-meta">' . Text::_('f10-toggle-full-screen-desc') . '</p>' . $editor;
		}

		return $editor;
	}
}