<?php

namespace App\Form\Field;

use MaiVu\Php\Form\Field\Number;

class CmsCurrencyInput extends Number
{
	public function toString()
	{
		$input = parent::toString();

		return <<<HTML
<div class="cms-currency-input">
	{$input}
	<div class="uk-help-text uk-text-meta"></div>
</div>
HTML;

	}
}