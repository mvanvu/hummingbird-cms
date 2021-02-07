<?php

namespace App\Form\Field;

use App\Helper\Assets;
use App\Helper\Service;
use App\Traits\ModalField;
use MaiVu\Php\Form\Field\Select;

class CmsImage extends Select
{
	use ModalField;

	protected $valueFilterCallBack = ['unique'];
	
	public function toString()
	{
		Assets::add('js/media-modal.js');
		$this->class = rtrim('uk-hidden ' . $this->class);

		return Service::view()->getPartial(
			'Form/Field/Image',
			[
				'field' => $this,
				'input' => parent::toString(),
			]
		);
	}
}