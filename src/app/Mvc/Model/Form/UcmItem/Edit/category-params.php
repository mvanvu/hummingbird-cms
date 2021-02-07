<?php

return [
	[
		'name'    => 'listLimit',
		'type'    => 'Number',
		'label'   => 'default-list-limit',
		'value'   => '',
		'class'   => 'uk-input',
		'hint'    => 'use-global-config',
		'min'     => 0,
		'max'     => 50,
		'filters' => ['uint'],
	],
	[
		'name'    => 'sortBy',
		'type'    => 'Select',
		'label'   => 'sort-by',
		'class'   => 'uk-select',
		'value'   => '',
		'options' => [
			''          => 'use-global-config',
			'latest'    => 'order-latest',
			'random'    => 'order-random',
			'titleAsc'  => 'order-title-asc',
			'titleDesc' => 'order-title-desc',
			'ordering'  => 'ordering',
		],
		'rules'   => ['Options'],
	],
	[
		'name'    => 'templateId',
		'type'    => 'CmsTemplate',
		'label'   => 'assign-for-template',
		'class'   => 'uk-select',
		'options' => [
			'0' => 'default-template',
		],
		'rules'   => ['Options'],
	],
];