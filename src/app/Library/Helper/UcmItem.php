<?php

namespace MaiVu\Hummingbird\Lib\Helper;

class UcmItem
{
	public static function prepareContext($context, $asArray = false)
	{
		$context = array_map('ucfirst', explode('-', $context));

		return $asArray ? $context : join('', $context);
	}
}