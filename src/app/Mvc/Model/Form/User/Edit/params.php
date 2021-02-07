<?php

return [
	[
		'name'    => 'avatar',
		'type'    => 'CmsImage',
		'label'   => 'avatar',
		'filters' => ['fileExists'],
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