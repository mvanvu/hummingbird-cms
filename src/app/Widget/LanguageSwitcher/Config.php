<?php

return [
	'name'        => 'LanguageSwitcher',
	'title'       => 'widget-language-switcher-title',
	'description' => 'widget-language-switcher-desc',
	'version'     => '1.0.0',
	'author'      => 'Mai Vu',
	'authorEmail' => 'rainy@joomtech.net',
	'authorUrl'   => 'https://www.joomtech.net',
	'updateUrl'   => null,
	'params'      => [
		[
			'name'    => 'displayLayout',
			'type'    => 'Select',
			'value'   => 'Dropdown',
			'label'   => 'display-layout',
			'options' => [
				'SubNav'   => 'sub-nav',
				'Flag'     => 'flags',
				'Dropdown' => 'dropdown',
			],
			'rules'   => ['Options'],
		],
	],
];
