<?php

namespace App\Form\Field;

use App\Helper\Assets;
use App\Helper\Date;
use App\Helper\Language;
use App\Helper\Text as CmsText;
use App\Helper\User;
use MaiVu\Php\Form\Field\Text;
use Throwable;

class CmsDatePicker extends Text
{
	protected $enableTime = false;
	protected $dateFormat = null;
	protected $dateTimeFormat = null;
	protected $showMonths = 1;

	/**
	 * @var array Eg: [ ['from' => '2020-01-01','to' => '2020-02-07'], ]
	 */
	protected $rangeDate = [];

	public function toString()
	{
		$langCode = Language::getActiveCode();
		$locale   = 'en-GB' === $langCode ? 'default' : $langCode;
		Assets::add(
			[
				'css/flatpickr.min.css',
				'js/flatpickr/' . $locale . '.js',
				'js/flatpickr.min.js',
			]
		);

		$enableTime  = $this->enableTime ? 'true' : 'false';
		$format      = $this->getFormat();
		$id          = $this->getId();
		$this->class = trim($this->class . ' ' . $id);
		$value       = $this->value;

		if (!empty($value) && '0000-00-00 00:00:00' !== $value)
		{
			$date = Date::getInstance($value, 'UTC');
			$date->setTimezone(User::getActive()->getTimezone());
			$this->value = $date->format('Y-m-d H:i:s');
		}

		$enable = json_encode($this->rangeDate);
		Assets::inlineJs(<<<JS
_$.ready(function($) {
    flatpickr('#{$id}', {
	    enableTime: {$enableTime},
	    dateFormat: 'Y-m-d H:i:ss',
	    locale: '{$locale}',    
	    altFormat: '{$format}',
	    enableSeconds: false,
	    altInput: true,
	    time_24hr: true,
	    showMonths: {$this->showMonths},
	    enable: {$enable},
	})
    
    $('#{$id}-picker-container > a').on('click', function() {
        const a = $(this);
      	if (a.hasClass('toggle-picker')) {
      	    const input = a.next('.input');
      	    input.toggleClass('focus');
      	    
      	    if (input.hasClass('focus')) {
      	        input.focus();
      	    } else {
      	        input.blur();
      	    }
      	} else {
      	    a.siblings('input').val('');
      	}
    });
});
JS
		);

		$input = ($this->required ? '' : '<a class="uk-form-icon" uk-icon="icon: close"></a>') . parent::toString();
		$input = <<<HTML
<div class="uk-position-relative" id="{$id}-picker-container">
	<a class="uk-form-icon uk-form-icon-flip toggle-picker" uk-icon="icon: calendar"></a>
	{$input}
</div>
HTML;

		// Revert value
		$this->value = $value;

		return $input;
	}

	protected function getFormat()
	{
		if (!$this->dateTimeFormat)
		{
			$this->dateTimeFormat = CmsText::_('locale.date-time-format');
		}

		if (!$this->dateFormat)
		{
			$this->dateFormat = CmsText::_('locale.date-format');
		}

		return $this->enableTime ? $this->dateTimeFormat : $this->dateFormat;
	}

	public function isValid()
	{
		if (!parent::isValid())
		{
			return false;
		}

		try
		{
			$this->value = Date::getInstance($this->value, User::getActive()->getTimezone())->toSql();

			return true;
		}
		catch (Throwable $e)
		{
			return false;
		}
	}
}