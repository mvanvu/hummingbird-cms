<?php

return [
	'name'        => 'Code',
	'title'       => 'widget-code-title',
	'description' => 'widget-code-desc',
	'version'     => '1.0.0',
	'author'      => 'Mai Vu',
	'authorEmail' => 'mvanvu@gmail.com',
	'authorUrl'   => 'https://github.com/mvanvu',
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