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
		'name'    => 'parentId',
		'type'    => 'CmsUcmItem',
		'options' => [
			'' => 'category-select',
		],
		'class'   => 'uk-select',
	],
];