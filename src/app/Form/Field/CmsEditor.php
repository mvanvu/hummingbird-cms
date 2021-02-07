<?php

namespace App\Form\Field;

use App\Helper\Editor;
use App\Helper\State;
use App\Helper\Uri;
use MaiVu\Php\Form\Field\TextArea;

class CmsEditor extends TextArea
{
	public function toString()
	{
		$uri          = clone Uri::getActive();
		$activeEditor = State::get('cms.editor.' . $uri->toPath(), 'TinyMCE');
		$activeTiny   = $activeCode = '';
		$tinyAttr     = $codeAttr = 'href="javascript:void(0)"';

		if (!in_array($activeEditor, ['TinyMCE', 'CodeMirror']))
		{
			$activeEditor = 'tinyMCE';
		}

		if ($activeEditor === 'TinyMCE')
		{
			Editor::initTinyMCE();
			$this->class = rtrim('js-editor-tinyMCE ' . $this->class);
			$activeTiny  = ' class="uk-active"';
			$codeAttr    = 'href="' . $uri->setQuery('cmsEditor', 'CodeMirror')->toString(true) . '" onclick="location.href = this.href"';
		}
		else
		{
			Editor::initCodeMirror();
			$this->class = rtrim('js-editor-codemirror ' . $this->class);
			$activeCode  = ' class="uk-active"';
			$tinyAttr    = 'href="' . $uri->setQuery('cmsEditor', 'TinyMCE')->toString(true) . '" onclick="location.href = this.href"';
		}

		$textarea = parent::toString();

		return <<<FIELD_HTML
<div class="cms-editor-group">
	<div class="uk-flex uk-flex-right">
		<ul class="uk-subnav uk-subnav-divider uk-margin-remove">
		    <li{$activeTiny}><a class="uk-text-capitalize" {$tinyAttr}>TinyMCE</a></li>
		    <li{$activeCode}><a class="uk-text-capitalize" {$codeAttr}>CodeMirror</a></li>
		</ul>
	</div>
	{$textarea}
</div>
FIELD_HTML;

	}
}