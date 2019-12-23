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
		'name'    => 'parentId',
		'type'    => 'CmsUcmItem',
		'options' => [
			'' => 'category-select',
		],
	],
];