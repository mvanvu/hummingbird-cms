<?php

return [
	[
		'name' => 'id',
		'type' => 'Hidden',
	],
	[
		'name' => 'context',
		'type' => 'Hidden',
	],
	[
		'name'  => 'level',
		'type'  => 'Hidden',
		'value' => 1,
	],
	[
		'name'  => 'ordering',
		'type'  => 'Hidden',
		'value' => 1,
	],
	[
		'name'      => 'title',
		'type'      => 'Text',
		'label'     => 'title',
		'required'  => true,
		'translate' => true,
		'filters'   => ['string', 'trim'],
	],
	[
		'name'    => 'state',
		'type'    => 'Select',
		'label'   => 'state',
		'value'   => 'P',
		'options' => [
			'U' => 'unpublished',
			'P' => 'published',
			'T' => 'trashed',
		],
		'rules'   => ['Options'],
	],
	[
		'name'      => 'summary',
		'type'      => 'TextArea',
		'label'     => 'summary-desc',
		'cols'      => 15,
		'rows'      => 3,
		'translate' => true,
		'filters'   => ['string', 'trim'],
	],
	[
		'name'      => 'description',
		'type'      => 'CmsEditor',
		'label'     => 'description',
		'cols'      => 50,
		'rows'      => 20,
		'translate' => true,
	],
];