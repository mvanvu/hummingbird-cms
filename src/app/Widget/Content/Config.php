<?php

return [
	'name'        => 'Content',
	'title'       => 'widget-content-title',
	'description' => 'widget-content-desc',
	'version'     => '1.0.0',
	'author'      => 'Mai vu (Rainy)',
	'authorEmail' => 'rainy@joomtech.net',
	'authorUrl'   => 'https://www.joomtech.net',
	'updateUrl'   => null,
	'params'      => [
		[
			'name'           => 'content',
			'type'           => 'CmsEditor',
			'label'          => 'content',
			'translate'      => true,
			'filters'        => ['html'],
			'dataAttributes' => [
				'editor-height' => 350,
			],
		],
	],
];
