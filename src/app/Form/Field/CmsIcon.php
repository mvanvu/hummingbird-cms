<?php

namespace App\Form\Field;

use App\Helper\Assets;
use App\Helper\IconSvg;
use App\Helper\Text;
use MaiVu\Php\Form\Field;

class CmsIcon extends Field
{
	protected $hint = '';

	public function toString()
	{
		static $iconsList = null;

		if (null === $iconsList)
		{
			Assets::add('js/icon.js');
			$iconContents = file_get_contents(PUBLIC_PATH . '/images/icons.svg');
			preg_match_all('/id=\"icon-([a-zA-Z0-9_\-]+)\"/', $iconContents, $matches);
			$iconsList = '';

			foreach (array_unique($matches[1]) as $icon)
			{
				$iconsList .= '<li><a data-icon="' . $icon . '">' . IconSvg::render($icon) . '&nbsp;' . $icon . '</a></li>';
			}
		}

		$hint     = Text::_($this->hint ?: 'icon-input-hint');
		$cssClass = ltrim($this->class . ' uk-input icon-input');
		$icon     = null;

		if ($value = $this->getValue())
		{
			$icon = '<span class="uk-form-icon">' . IconSvg::render($value) . '</span>';
		}

		return <<<HTML
<div class="uk-position-relative">
	{$icon}
	<input class="{$cssClass}" name="{$this->getName()}" value="{$value}" placeholder="{$hint}" id="{$this->getId()}" autocomplete="off"/>
	<div class="icon-drop uk-card uk-card-default uk-card-body" hidden>
    	<ul class="uk-nav uk-dropdown-nav icon-list">{$iconsList}</ul>
	</div>
</div>
HTML;
	}
}