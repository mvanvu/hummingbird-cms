<?php

return [
	[
		'name'    => 'allowUserComment',
		'type'    => 'Switcher',
		'label'   => 'allow-user-comment',
		'value'   => 'Y',
		'filters' => ['yesNo'],
	],
	[
		'name'    => 'commentAsGuest',
		'type'    => 'Switcher',
		'label'   => 'allow-user-comment-as-guest',
		'value'   => 'Y',
		'showOn'  => 'allowUserComment:Y',
		'filters' => ['yesNo'],
	],
	[
		'name'    => 'autoPublishComment',
		'type'    => 'Switcher',
		'label'   => 'auto-publish-comment',
		'value'   => 'Y',
		'showOn'  => 'allowUserComment:Y',
		'filters' => ['yesNo'],
	],
	[
		'name'    => 'commentWithEmoji',
		'type'    => 'Switcher',
		'label'   => 'comment-with-emoji',
		'value'   => 'Y',
		'showOn'  => 'allowUserComment:Y',
		'filters' => ['yesNo'],
	],
	[
		'name'    => 'templateId',
		'type'    => 'CmsTemplate',
		'label'   => 'assign-for-template',
		'class'   => 'uk-select',
		'options' => [
			'0' => 'default-template',
		],
		'rules'   => ['Options'],
	],
];