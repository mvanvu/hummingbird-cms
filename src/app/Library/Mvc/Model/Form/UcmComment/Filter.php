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
		'name' => 'referenceId',
		'type' => 'CmsModalUcmItem',
	],
];