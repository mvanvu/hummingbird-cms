<?php

return [
	[
		'name'          => 'allowUserComment',
		'type'          => 'Check',
		'label'         => 'allow-user-comment',
		'checkboxValue' => 'Y',
		'value'         => 'N',
		'filters'       => ['yesNo'],
	],
	[
		'name'          => 'commentAsGuest',
		'type'          => 'Check',
		'label'         => 'allow-user-comment-as-guest',
		'checkboxValue' => 'Y',
		'value'         => 'N',
		'showOn'        => 'allowUserComment : is checked',
		'filters'       => ['yesNo'],
	],
	[
		'name'          => 'autoPublishComment',
		'type'          => 'Check',
		'label'         => 'auto-publish-comment',
		'checkboxValue' => 'Y',
		'value'         => 'N',
		'showOn'        => 'allowUserComment : is checked',
		'filters'       => ['yesNo'],
	],
	[
		'name'          => 'commentWithEmoji',
		'type'          => 'Check',
		'label'         => 'comment-with-emoji',
		'checkboxValue' => 'Y',
		'value'         => 'N',
		'showOn'        => 'allowUserComment : is checked',
		'filters'       => ['yesNo'],
	],
];