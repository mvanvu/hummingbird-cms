<?php

namespace App\Mvc\Model;

use App\Helper\Uri;

class CategoryBase extends Nested
{
	public function initialize()
	{
		parent::initialize();
		$referenceModel = get_class($this);
		$params         = [
			'conditions' => '',
			'bind'       => [],
			'order'      => 'lft ASC',
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
}