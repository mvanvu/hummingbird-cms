<?php

return [
	[
		'name'      => 'route',
		'type'      => 'Text',
		'label'     => 'route',
		'translate' => true,
		'filters'   => ['path'],
	],
	[
		'name'  => 'parentId',
		'type'  => 'CmsUcmItem',
		'label' => 'parent-level',
		'rules' => ['Options'],
	],
	[
		'name'      => 'image',
		'type'      => 'CmsImage',
		'multiple'  => true,
		'translate' => true,
		'filters'   => ['fileExists'],
	],
	[
		'name'     => 'tags',
		'type'     => 'CmsTag',
		'label'    => 'tags',
		'multiple' => true,
		'rules'    => ['Options'],
	],
];