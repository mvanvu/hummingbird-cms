<?php

return [
	[
		'name'  => 'timezone',
		'type'  => 'CmsTimezone',
		'label' => 'timezone',
		'rules' => ['Options'],
		'value' => 'UTC',
	],
	[
		'name'    => 'avatar',
		'type'    => 'CmsImage',
		'label'   => 'avatar',
		'filters' => ['fileExists'],
	],
];