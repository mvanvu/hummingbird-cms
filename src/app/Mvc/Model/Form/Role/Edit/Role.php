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
		'name'    => 'description',
		'type'    => 'TextArea',
		'label'   => 'description',
		'filters' => ['string', 'trim'],
		'class'   => 'uk-textarea',
		'rows'    => 5,
		'cols'    => 25,
	],
	[
		'name'    => 'type',
		'type'    => 'Switcher',
		'label'   => 'role-admin-login',
		'filters' => ['yesNo'],
		'value'   => 'Y',
	],
];