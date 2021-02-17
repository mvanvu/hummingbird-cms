<?php

return [
	[
		'name'      => 'avatar',
		'type'      => 'CmsUpload',
		'label'     => 'avatar',
		'isPrivate' => false,
	],
	[
		'name'  => 'timezone',
		'type'  => 'CmsTimezone',
		'label' => 'timezone',
		'value' => 'UTC',
		'class' => 'uk-select',
		'rules' => ['Options'],
	],
];