<?php

return [
	[
		'name' => 'id',
		'type' => 'Hidden',
	],
	[
		'name'     => 'name',
		'type'     => 'Text',
		'label'    => 'name',
		'required' => true,
		'filters'  => ['string', 'trim'],
		'class'    => 'uk-input',
	],
	[
		'name' => 'resources',
		'type' => 'CmsTemplateTree',
	],
];