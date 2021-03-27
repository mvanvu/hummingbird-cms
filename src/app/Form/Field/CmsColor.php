<?php

namespace App\Form\Field;

use App\Helper\Assets;
use App\Helper\Text as T;
use MaiVu\Php\Form\Field\Text;

class CmsColor extends Text
{
	protected $mode = 'HEX';

	public function toString()
	{
		T::script('save');
		T::script('clear');
		T::script('cancel');
		Assets::add(
			[
				'css/monolith.min.css',
				'js/pickr.min.js',
				'js/color.js',
			]
		);
		$input = parent::toString();
		$color = $this->getValue() ?: '#fff';

		return <<<HTML
<div class="picker-container uk-inline">
    <a class="uk-form-icon uk-form-icon-flip color-picker" href="javascript:void(0)" uk-icon="icon: paint-bucket"
       data-color="{$color}"
       data-mode="{$this->mode}"></a>
	{$input}
</div>
HTML;
	}
}
