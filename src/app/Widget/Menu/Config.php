<?php

return [
	'name'        => 'Menu',
	'title'       => 'widget-menu-title',
	'description' => 'widget-menu-desc',
	'version'     => '1.0.0',
	'author'      => 'Mai Vu',
	'authorEmail' => 'rainy@joomtech.net',
	'authorUrl'   => 'https://www.joomtech.net',
	'updateUrl'   => null,
	'params'      => [
		[
			'name'    => 'menuType',
			'type'    => 'CmsMenuType',
			'label'   => 'menu-type-select',
			'value'   => '',
			'options' => [
				'' => 'menu-type-select',
			],
			'rules'   => ['Options'],
		],
	],
];