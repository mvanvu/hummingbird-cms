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
		'name' => 'referenceId',
		'type' => 'CmsModalUcmItem',
	],
];