<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Form\FormsManager;

class PostCategory extends Nested
{
	public $context = 'post-category';
	public $itemContext = 'post';
	public $hasRoute = true;

	public function initialize()
	{
		parent::initialize();
		$referenceModel = get_class($this);
		$params         = [
			'conditions' => '',
			'bind'       => [],
			'order'      => 'ordering ASC',
		];

		if (Uri::isClient('site'))
		{
			$params['conditions']    = 'state = :state:';
			$params['bind']['state'] = 'P';
		}
		else
		{
			$params['conditions']    = 'state <> :state:';
			$params['bind']['state'] = 'T';
		}

		$this->belongsTo(['context', 'parentId'], $referenceModel, ['context', 'id'],
			[
				'alias'    => 'parent',
				'reusable' => true,
				'params'   => $params,
			]
		);

		$this->hasMany(['context', 'id'], $referenceModel, ['context', 'parentId'],
			[
				'alias'    => 'children',
				'reusable' => true,
				'params'   => $params,
			]
		);
	}

	public function prepareFormsManager(FormsManager $formsManager)
	{
		$asideForm = $formsManager->get('aside');
		$asideForm->remove('tags');
		$asideForm->getField('parentId')->set('context', 'post-category');
	}
}