<?php

return [
	'name'        => 'FlashNews',
	'title'       => 'widget-flash-news-title',
	'description' => 'widget-flash-news-desc',
	'version'     => '1.0.0',
	'author'      => 'Mai Vu',
	'authorEmail' => 'rainy@joomtech.net',
	'authorUrl'   => 'https://www.joomtech.net',
	'updateUrl'   => null,
	'params'      => [
		[
			'name'     => 'categoryIds',
			'type'     => 'CmsModalUcmItem',
			'context'  => 'post-category',
			'multiple' => true,
			'filters'  => ['uint'],
		],
		[
			'name'     => 'postsNum',
			'type'     => 'Number',
			'label'    => 'limit-posts-number',
			'multiple' => true,
			'filters'  => ['uint'],
			'min'      => 1,
			'value'    => 5,
		],
		[
			'name'    => 'displayLayout',
			'type'    => 'Select',
			'label'   => 'display-layout',
			'value'   => 'FlashNews',
			'options' => [
				'FlashNews'  => 'slider-thumb-nav',
				'SliderNews' => 'sub-slider',
				'BlogList'   => 'blog-list',
				'BlogStack'  => 'blog-stack',
			],
			'rules'   => ['Options'],
		],
		[
			'name'    => 'orderBy',
			'type'    => 'Select',
			'label'   => 'order-by',
			'value'   => 'latest',
			'options' => [
				'latest'    => 'order-latest',
				'random'    => 'order-random',
				'titleAsc'  => 'order-title-asc',
				'titleDesc' => 'order-title-desc',
			],
			'rules'   => ['Options'],
		],
	],
];
