<?php

namespace App\Helper;

use App\Factory\Factory;

class ReCaptcha
{
	const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	public static function render()
	{
		$siteKey = Config::get('reCaptchaSiteKey');

		if (empty($siteKey))
		{
			return null;
		}

		static $init = false;

		if (!$init)
		{
			$init = true;
			Assets::add('https://www.google.com/recaptcha/api.js?render=' . $siteKey . '&f=api.js');
			Assets::inlineJs(<<<JAVASCRIPT
grecaptcha.ready(function() {
    grecaptcha.execute('{$siteKey}').then(function(token) {
       Array.from(document.getElementsByClassName('g-recaptcha-response')).forEach(function(element) {
            element.value = token;
       });
    });
});
JAVASCRIPT
			);
		}

		return '<input name="reCaptchaResponse" class="g-recaptcha-response" type="hidden" value=""/>';
	}

	public static function isValid($warn = false)
	{
		$secretKey = Config::get('reCaptchaSecretKey');

		if (empty($secretKey) || preg_match('/^https?:\/\/localhost/', Uri::getHost()))
		{
			return true;
		}

		$request = Factory::getService('request');
		$value   = $request->get('reCaptchaResponse');

		if (!function_exists('curl_init') || empty($value))
		{
			if ($warn)
			{
				Service::flashSession()->warning(Text::_('invalid-re-captcha-msg'));
			}

			return false;
		}

		$curl = curl_init(ReCaptcha::VERIFY_URL);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt(
			$curl,
			CURLOPT_POSTFIELDS,
			http_build_query(
				[
					'secret'   => $secretKey,
					'response' => $value,
					'remoteip' => $request->getClientAddress(),
				]
			)
		);

		$response = @json_decode(curl_exec($curl), true);

		if (true === @$response['success'])
		{
			return true;
		}

		if ($warn)
		{
			Service::flashSession()->warning(Text::_('invalid-re-captcha-msg'));
		}

		return false;
	}
}