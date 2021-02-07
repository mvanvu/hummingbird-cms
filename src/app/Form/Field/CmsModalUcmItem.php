<?php

namespace App\Form\Field;

use App\Helper\Assets;
use App\Helper\Service;
use App\Helper\Text;
use App\Mvc\Model\UcmItem;
use App\Traits\ModalField;
use MaiVu\Php\Form\Field\Select;

class CmsModalUcmItem extends Select
{
	use ModalField;
	
	protected $context;
	protected $multiple = false;
	protected $modalFull = false;

	public function toString()
	{
		$value    = $this->getParsedValue();
		$id       = $this->getId();
		$multiple = $this->multiple ? 'true' : 'false';

		if ($this->multiple)
		{
			$selectText = Text::_($this->context . '-items-select');
		}
		else
		{
			$selectText = Text::_($this->context . '-item-select');
		}

		if (empty($value))
		{
			$items = [];
		}
		else
		{
			$items = UcmItem::find('id IN (' . implode(',', $value) . ')')->toArray();
		}

		$dispatcher = Service::dispatcher();
		$request    = Service::request();

		if ($dispatcher->getControllerName() === 'admin_menu'
			&& $request->has('type')
			&& $request->has('id')
		)
		{
			// This came from the menu page
			$this->modalFull = true;
		}

		Assets::add('js/ucm-item-modal.js');
		Assets::inlineJs('cmsCore.initUcmElementModal(\'' . $id . '\');');
		$this->class = rtrim('uk-hidden ' . $this->class);

		return Service::view()->getPartial('Form/Field/ModalUcmItem',
			[
				'id'         => $id,
				'context'    => $this->context,
				'name'       => $this->getName(),
				'value'      => $value,
				'modalClass' => $this->modalFull ? 'uk-modal-full' : 'uk-modal-container',
				'modalClose' => $this->modalFull ? 'uk-modal-close-full' : 'uk-modal-close-default',
				'items'      => $items,
				'selectText' => $selectText,
				'multiple'   => $multiple,
				'input'      => parent::toString(),
			]
		);
	}
}