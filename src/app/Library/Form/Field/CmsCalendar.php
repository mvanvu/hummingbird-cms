<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Helper\Asset;
use MaiVu\Hummingbird\Lib\Helper\Date;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Form\Field;
use Exception;

class CmsCalendar extends Field
{
	protected $showTime = false;
	protected $hint = null;

	public function toString()
	{
		$valueInput = $valueAlias = $this->getValue();

		if (!empty($valueInput))
		{
			try
			{
				$date       = new Date($valueInput);
				$valueInput = $date->toDisplay('Y-m-d H:i:s', false);

				if ($this->showTime)
				{
					$format = Text::_('local.date-time-format');
				}
				else
				{
					$format = Text::_('local.date-format');
				}

				$valueAlias = $date->toDisplay($format);

			}
			catch (Exception $e)
			{
				$valueInput = null;
				$valueAlias = null;
			}
		}

		Asset::calendar();
		$idInput      = $this->getId();
		$nameInput    = $this->getName();
		$idAlias      = $idInput . '-alias';
		$showTime     = $this->showTime ? 'true' : 'false';
		$jsDateFormat = Text::_('locale.js-date-format');
		$dataCalendar = htmlspecialchars(
			json_encode(
				[
					'input'  => $idInput,
					'alias'  => $idAlias,
					'format' => $jsDateFormat,
					'time'   => $showTime,
				]
			),
			ENT_COMPAT,
			'UTF-8'
		);

		$input     = '<input name="' . $nameInput . '" id="' . $idInput . '" type="hidden" value="' . $valueInput . '"';
		$aliasAttr = 'readonly';

		if ($this->required)
		{
			$input     .= ' required';
			$aliasAttr .= ' required';
		}

		if ($this->readonly)
		{
			$input .= ' readonly';
		}

		if ($this->hint)
		{
			$aliasAttr .= ' placeholder="' . htmlspecialchars($this->hint, ENT_COMPAT, 'UTF-8') . '"';
		}

		$input .= '/>';

		return <<<HTML
<div class="uk-position-relative" data-calendar="{$dataCalendar}">
    <a class="uk-form-icon uk-form-icon-flip" href="#" uk-icon="icon: calendar"></a>
    <input class="uk-input" id="{$idAlias}" type="text" value="{$valueAlias}" {$aliasAttr}/>
    {$input}
</div>
HTML;

	}
}