<?php

return [
	[
		'name'    => 'context',
		'type'    => 'CmsSQL',
		'class'   => 'uk-select',
		'query'   => 'SELECT DISTINCT context AS value, context AS text FROM #__logs',
		'options' => [
			'' => 'log-type-select',
		],
	],
];