<?php

return [
	[
		'name'      => 'metaTitle',
		'type'      => 'Text',
		'label'     => 'meta-title',
		'translate' => true,
		'filters'   => ['string', 'trim'],
	],
	[
		'name'      => 'metaDesc',
		'type'      => 'TextArea',
		'label'     => 'meta-desc',
		'cols'      => 15,
		'rows'      => 2,
		'translate' => true,
		'filters'   => ['string', 'trim'],
	],
	[
		'name'      => 'metaKeys',
		'type'      => 'Text',
		'label'     => 'meta-keys',
		'cols'      => 15,
		'rows'      => 2,
		'translate' => true,
		'filters'   => ['string', 'trim'],
	],
	[
		'name'    => 'metaRobots',
		'type'    => 'Select',
		'label'   => 'robots',
		'options' => [
			''                  => 'robot-index-follow',
			'noindex, follow'   => 'robot-no-index-follow',
			'index, nofollow'   => 'robot-index-no-follow',
			'noindex, nofollow' => 'robot-no-index-no-follow',
		],
		'rules'   => ['Options'],
	],
];