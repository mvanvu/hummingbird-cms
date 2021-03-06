<?php

return [
	[
		'name'  => 'siteLanguage',
		'type'  => 'CmsLanguage',
		'label' => 'site-language',
		'value' => 'en-GB',
		'rules' => ['Options'],
		'class' => 'uk-select uk-width-medium',
	],
	[
		'name'  => 'administratorLanguage',
		'type'  => 'CmsLanguage',
		'label' => 'administrator-language',
		'value' => 'en-GB',
		'rules' => ['Options'],
		'class' => 'uk-select uk-width-medium',
	],
	[
		'name'  => 'mainCurrency',
		'type'  => 'CmsCurrencyList',
		'label' => 'main-currency',
		'value' => 'USD',
		'rules' => ['Options'],
		'class' => 'uk-select uk-width-medium',
	],
	[
		'name'  => 'timezone',
		'type'  => 'CmsTimezone',
		'label' => 'timezone',
		'rules' => ['Options'],
		'class' => 'uk-select uk-width-medium',
	],
	[
		'name'    => 'multilingual',
		'type'    => 'Switcher',
		'label'   => 'multilingual-mode',
		'value'   => 'Y',
		'filters' => ['yesNo'],
	],
];