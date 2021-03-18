<?php

return [
	[
		'name'     => 'symbol',
		'type'     => 'Text',
		'label'    => 'currency-symbol',
		'class'    => 'uk-input',
		'value'    => '$',
		'required' => true,
	],
	[
		'name'     => 'decimals',
		'type'     => 'Number',
		'label'    => 'currency-decimals',
		'class'    => 'uk-input',
		'required' => true,
		'value'    => 2,
		'min'      => 0,
		'max'      => 4,
		'filters'  => ['uint'],
	],
	[
		'name'     => 'separator',
		'type'     => 'Text',
		'label'    => 'currency-separator',
		'class'    => 'uk-input',
		'value'    => ',',
		'required' => true,
	],
	[
		'name'     => 'point',
		'type'     => 'Text',
		'label'    => 'currency-point',
		'class'    => 'uk-input',
		'value'    => '.',
		'required' => true,
	],
	[
		'name'     => 'format',
		'type'     => 'Text',
		'label'    => 'currency-format',
		'class'    => 'uk-input',
		'value'    => '{symbol}{value}',
		'required' => true,
	],
];