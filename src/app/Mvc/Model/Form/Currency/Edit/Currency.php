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
		'label'    => 'currency-code',
		'required' => true,
		'filters'  => ['string', 'trim'],
		'class'    => 'uk-input',
	],
	[
		'name'     => 'rate',
		'type'     => 'Text',
		'label'    => 'currency-rate',
		'required' => true,
		'filters'  => ['ufloat'],
		'class'    => 'uk-input',
	],
];