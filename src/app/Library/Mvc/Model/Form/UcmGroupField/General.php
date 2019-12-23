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
		'name'      => 'title',
		'type'      => 'Text',
		'label'     => 'title',
		'required'  => true,
		'translate' => true,
		'filters'   => ['string', 'trim'],
	],
];