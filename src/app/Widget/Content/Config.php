<?php

return [
	'name'        => 'Content',
	'title'       => 'widget-content-title',
	'description' => 'widget-content-desc',
	'version'     => '1.0.0',
	'author'      => 'Mai Vu',
	'authorEmail' => 'mvanvu@gmail.com',
	'authorUrl'   => 'https://github.com/mvanvu',
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
