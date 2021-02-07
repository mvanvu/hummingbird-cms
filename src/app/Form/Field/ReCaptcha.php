<?php

namespace App\Form\Field;

use App\Helper\ReCaptcha as ReCaptchaHelper;
use App\Helper\Text;
use MaiVu\Php\Form\Field;

class ReCaptcha extends Field
{
	public function toString()
	{
		return ReCaptchaHelper::render();
	}

	public function isValid()
	{
		if (ReCaptchaHelper::isValid())
		{
			return true;
		}

		$this->messages[] = Text::_('invalid-re-captcha-msg');

		return false;
	}
}