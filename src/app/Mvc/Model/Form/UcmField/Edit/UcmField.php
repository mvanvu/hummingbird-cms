<?php

use App\Helper\Text;

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
		'name'           => 'label',
		'type'           => 'Text',
		'label'          => 'label',
		'required'       => true,
		'translate'      => true,
		'filters'        => ['string', 'trim'],
		'class'          => 'uk-input',
		'dataAttributes' => [
			'msg-required' => Text::_('field-label-required-msg'),
		],
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
		'class'    => 'uk-select',
	],
	[
		'name'     => 'type',
		'type'     => 'Select',
		'label'    => 'type',
		'required' => true,
		'value'    => 'Text',
		'options'  => [
			'Check'         => 'check-field',
			'CheckList'     => 'check-list-field',
			'CmsEditor'     => 'editor-field',
			'CmsTinyMCE'    => 'TinyMCE',
			'CmsCodeMirror' => 'CodeMirror',
			'Email'         => 'email-field',
			'Radio'         => 'radio-field',
			'Select'        => 'select-field',
			'Switcher'      => 'switcher-field',
			'Text'          => 'text-field',
			'TextArea'      => 'textarea-field',
		],
		'rules'    => ['Options'],
		'class'    => 'uk-select',
	],
	[
		'name'    => 'groupId',
		'type'    => 'CmsGroupField',
		'label'   => 'group',
		'options' => [
			'0' => 'no-group',
		],
		'filters' => ['uint'],
		'rules'   => ['Options'],
		'class'   => 'uk-select',
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
		'class'       => 'uk-select',
	],
	[
		'name'    => 'name',
		'type'    => 'Text',
		'label'   => 'name',
		'filters' => ['inputName'],
		'class'   => 'uk-input',
	],
];