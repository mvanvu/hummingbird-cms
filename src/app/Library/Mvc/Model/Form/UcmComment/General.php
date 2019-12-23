<?php

return [
	[
		'name' => 'id',
		'type' => 'Hidden',
	],
	[
		'name' => 'referenceContext',
		'type' => 'Hidden',
	],
	[
		'name' => 'referenceId',
		'type' => 'Hidden',
	],
	[
		'name'  => 'parentId',
		'type'  => 'Hidden',
		'value' => 0,
	],
	[
		'name' => 'userIp',
		'type' => 'Hidden',
	],
	[
		'name'     => 'userName',
		'type'     => 'Text',
		'label'    => 'user-name',
		'required' => true,
	],
	[
		'name'     => 'userEmail',
		'type'     => 'Email',
		'label'    => 'user-email',
		'required' => true,
		'rules'    => ['email'],
		'messages' => [
			'email' => 'comment-invalid-email-msg',
		]
	],
	[
		'name'     => 'userComment',
		'type'     => 'TextArea',
		'label'    => 'user-comment',
		'cols'     => 50,
		'rows'     => 15,
		'required' => true,
		'filters'  => ['string', 'trim'],
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
		'name'  => 'userVote',
		'type'  => 'CmsCommentVote',
		'label' => 'user-vote',
		'rules' => ['Options'],
		'value' => 0,
	],
];