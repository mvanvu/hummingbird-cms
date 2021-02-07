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
		'class'    => 'uk-input',
	],
	[
		'name'     => 'userEmail',
		'type'     => 'Email',
		'label'    => 'user-email',
		'required' => true,
		'rules'    => ['Email' => 'comment-invalid-email-msg'],
		'class'    => 'uk-input',
	],
	[
		'name'     => 'userComment',
		'type'     => 'TextArea',
		'label'    => 'user-comment',
		'cols'     => 50,
		'rows'     => 15,
		'required' => true,
		'filters'  => ['string', 'trim'],
		'class'    => 'uk-textarea',
	],
	[
		'name'    => 'state',
		'type'    => 'Select',
		'label'   => 'state',
		'value'   => 'P',
		'options' => [
			''  => 'state-select',
			'U' => 'unpublished',
			'P' => 'published',
			'T' => 'trashed',
		],
		'class'   => 'uk-select',
		'rules'   => ['Options'],
	],
	[
		'name'  => 'userVote',
		'type'  => 'CmsCommentVote',
		'label' => 'user-vote',
		'rules' => ['Options'],
		'class' => 'uk-select',
		'value' => 0,
	],
];