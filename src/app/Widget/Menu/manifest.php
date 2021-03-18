<?php

return [
	'name'        => 'Menu',
	'title'       => 'widget-menu-title',
	'description' => 'widget-menu-desc',
	'version'     => '1.0.0',
	'author'      => 'Mai Vu',
	'authorEmail' => 'mvanvu@gmail.com',
	'authorUrl'   => 'https://github.com/mvanvu',
	'updateUrl'   => null,
	'params'      => [
		[
			'name'    => 'menuType',
			'type'    => 'CmsMenuType',
			'label'   => 'menu-type-select',
			'class'   => 'uk-select',
			'value'   => '',
			'options' => [
				'' => 'menu-type-select',
			],
			'rules'   => ['Options'],
		],
	],
];