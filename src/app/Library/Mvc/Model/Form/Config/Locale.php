<?php

return [
	[
		'name'  => 'siteLanguage',
		'type'  => 'CmsLanguage',
		'label' => 'site-language',
		'value' => 'en-GB',
		'rules' => ['Options'],
	],
	[
		'name'  => 'administratorLanguage',
		'type'  => 'CmsLanguage',
		'label' => 'administrator-language',
		'value' => 'en-GB',
		'rules' => ['Options'],
	],
	[
		'name'          => 'multilingual',
		'type'          => 'Check',
		'label'         => 'multilingual-mode',
		'checkboxValue' => 'Y',
		'value'         => 'N',
		'filters'       => ['yesNo'],
	],
	[
		'name'  => 'timezone',
		'type'  => 'CmsTimezone',
		'label' => 'timezone',
		'rules' => ['Options'],
	],
];