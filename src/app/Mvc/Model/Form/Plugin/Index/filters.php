<?php

return [
	[
		'name'    => 'group',
		'type'    => 'CmsSQL',
		'class'   => 'uk-select',
		'options' => ['' => 'group-select'],
		'query'   => 'SELECT DISTINCT `group` AS value, `group` AS text FROM #__plugins',
	],
	[
		'name'    => 'active',
		'type'    => 'Select',
		'class'   => 'uk-select',
		'options' => [
			''  => 'state-select',
			'Y' => 'yes',
			'N' => 'no',
		],
	]
];