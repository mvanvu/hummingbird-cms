<?php

return [
	[
		'name' => 'id',
		'type' => 'Hidden',
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
		'name'      => 'slug',
		'type'      => 'Text',
		'label'     => 'slug',
		'translate' => true,
		'filters'   => ['slug'],
	],
];