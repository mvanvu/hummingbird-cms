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
];