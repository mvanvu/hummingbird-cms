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
		'name'  => 'level',
		'type'  => 'Hidden',
		'value' => 1,
	],
	[
		'name'  => 'ordering',
		'type'  => 'Hidden',
		'value' => 1,
	],
	[
		'name'           => 'title',
		'type'           => 'Text',
		'label'          => 'title',
		'required'       => true,
		'translate'      => true,
		'filters'        => ['string', 'trim'],
		'class'          => 'uk-input',
		'dataAttributes' => [
			'msg-required' => Text::_('title-required-msg'),
		],
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
		'class'   => 'uk-select',
		'rules'   => ['Options'],
	],
	[
		'name'      => 'description',
		'type'      => 'CmsEditor',
		'label'     => 'description',
		'cols'      => 50,
		'rows'      => 20,
		'translate' => true,
	],
];