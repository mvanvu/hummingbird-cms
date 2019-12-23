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
		'name'      => 'label',
		'type'      => 'Text',
		'label'     => 'label',
		'required'  => true,
		'translate' => true,
		'filters'   => ['string', 'trim'],
	],
	[
		'name'     => 'state',
		'type'     => 'Select',
		'label'    => 'state',
		'required' => true,
		'value'    => 'P',
		'options'  => [
			'U' => 'unpublished',
			'P' => 'published',
			'T' => 'trashed',
		],
		'rules'    => ['Options'],
	],
	[
		'name'     => 'type',
		'type'     => 'Select',
		'label'    => 'type',
		'required' => true,
		'value'    => 'text',
		'options'  => [
			'Check'         => 'check-field',
			'CmsEditor'     => 'editor-field',
			'CmsEditorCode' => 'editor-codemirror-field',
			'Email'         => 'email-field',
			'Radio'         => 'radio-field',
			'Select'        => 'select-field',
			'Text'          => 'text-field',
			'TextArea'      => 'textarea-field',
		],
		'rules'    => ['Options'],
	],
	[
		'name'    => 'groupId',
		'type'    => 'CmsGroupField',
		'label'   => 'group',
		'options' => [
			0 => 'no-group',
		],
		'filters' => ['uint'],
		'rules'   => ['Options'],
	],
	[
		'name'        => 'cid',
		'type'        => 'CmsUcmItem',
		'label'       => 'post-category-items-select',
		'description' => 'field-category-desc',
		'multiple'    => true,
		'showRoot'    => false,
		'filters'     => ['uint'],
		'rules'       => ['Options'],
	],
	[
		'name'    => 'name',
		'type'    => 'Text',
		'label'   => 'name',
		'filters' => ['inputName'],
	],
];