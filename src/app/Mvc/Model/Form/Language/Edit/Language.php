<?php

return [
	[
		'name' => 'id',
		'type' => 'Hidden',
	],
	[
		'name'     => 'name',
		'type'     => 'Text',
		'label'    => 'name',
		'required' => true,
		'filters'  => ['string', 'trim'],
		'class'    => 'uk-input',
	],
	[
		'name'     => 'state',
		'type'     => 'Select',
		'label'    => 'state',
		'value'    => 'P',
		'options'  => [
			'U' => 'unpublished',
			'P' => 'published',
			'T' => 'trashed',
		],
		'class'    => 'uk-select',
		'rules'    => ['Options'],
		'required' => true,
	],
	[
		'name'     => 'code',
		'type'     => 'Text',
		'label'    => 'lang-code',
		'required' => true,
		'filters'  => ['string', 'trim'],
		'class'    => 'uk-input',
	],
	[
		'name'     => 'iso',
		'type'     => 'CmsLanguageIso',
		'label'    => 'lang-iso',
		'required' => true,
		'class'    => 'uk-select',
		'rules'    => ['Options'],
		'options'  => ['' => 'lang-iso-select']
	],
	[
		'name'     => 'sef',
		'type'     => 'Text',
		'label'    => 'lang-sef',
		'required' => true,
		'filters'  => ['slug'],
		'class'    => 'uk-input',
	],
	[
		'name'     => 'direction',
		'type'     => 'Select',
		'label'    => 'lang-direction',
		'required' => true,
		'class'    => 'uk-select',
		'value'    => 'LTR',
		'rules'    => ['Options'],
		'options'  => [
			'LTR' => 'lang-ltr',
			'RTL' => 'lang-rtl',
		],
	],
];