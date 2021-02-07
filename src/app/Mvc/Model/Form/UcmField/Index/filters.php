<?php

return [
	[
		'name'    => 'state',
		'type'    => 'Select',
		'options' => [
			''  => 'state-select',
			'U' => 'unpublished',
			'P' => 'published',
			'T' => 'trashed',
		],
		'class'   => 'uk-select',
	],
	[
		'name'    => 'groupId',
		'type'    => 'CmsGroupField',
		'value'   => '',
		'options' => [
			''  => 'group-field-select',
			'T' => 'no-group',
		],
		'class'   => 'uk-select',
	],
];