<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Helper\Asset;
use MaiVu\Hummingbird\Lib\Helper\Text as CmsText;
use MaiVu\Php\Registry;

class CmsOptionRepeat extends InputAbstract
{
	protected $inputType = 'hidden';
	protected $inputClass = 'uk-hidden';

	public function cleanValue($value)
	{
		$value = Registry::parseData($value);

		return parent::cleanValue($value);
	}

	public function toString()
	{
		Asset::addFile('options-repeat.js');
		$valueHint = htmlspecialchars(CmsText::_('option-value'), ENT_COMPAT, 'UTF-8');
		$textHint  = htmlspecialchars(CmsText::_('option-text'), ENT_COMPAT, 'UTF-8');
		$id        = $this->getId();
		$input     = parent::toString();

		return <<<HTML
<div class="options-repeat-container" data-input-id="{$id}" uk-sortable>
	<div class="uk-padding-small uk-background-muted uk-margin-small row">
	    <div class="uk-grid-small uk-child-width-1-2" uk-grid>
	        <div>
	            <input class="uk-input uk-form-small value" placeholder="{$valueHint}"/>
	        </div>
	        <div>
	            <input class="uk-input uk-form-small text" placeholder="{$textHint}"/>
	        </div>
	    </div>
	    <div class="uk-flex uk-flex-right uk-margin-small-top">
	        <ul class="uk-iconnav">
	            <li><a class="add" href="#" uk-icon="icon: plus"></a></li>
	            <li><a class="remove" href="#" uk-icon="icon: trash"></a></li>
	        </ul>
	    </div>	    
	</div>
	{$input}
</div>
HTML;

	}
}
