<?php

return [
	'name'        => 'Code',
	'title'       => 'widget-code-title',
	'description' => 'widget-code-desc',
	'version'     => '1.0.0',
	'author'      => 'Mai vu (Rainy)',
	'authorEmail' => 'rainy@joomtech.net',
	'authorUrl'   => 'https://www.joomtech.net',
	'updateUrl'   => null,
	'params'      => [
		[
			'name'      => 'content',
			'type'      => 'CmsEditorCode',
			'label'     => 'widget-code-title',
			'translate' => true,
			'filters'   => ['html'],
		],
	],
];