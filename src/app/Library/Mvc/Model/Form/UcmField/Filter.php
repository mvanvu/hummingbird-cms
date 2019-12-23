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
	],
	[
		'name'    => 'groupId',
		'type'    => 'CmsGroupField',
		'value'   => '',
		'options' => [
			'' => 'group-field-select',
			0  => 'no-group',
		],
	],
];