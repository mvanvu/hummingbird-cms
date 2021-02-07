<?php

namespace App\Form\Field;

use App\Helper\Service;
use App\Mvc\Model\UcmGroupField;
use MaiVu\Php\Form\Field\Select;

class CmsGroupField extends Select
{
	protected $context = null;

	public function getOptions()
	{
		$options = parent::getOptions();
		$context = $this->context ?: Service::dispatcher()->getParam('context');

		if (!empty($context))
		{
			$groups = UcmGroupField::find(
				[
					'conditions' => 'context = :context:',
					'bind'       => [
						'context' => $context,
					],
				]
			);

			if ($groups->count())
			{
				foreach ($groups as $group)
				{
					$options[$group->id] = $group->title;
				}
			}
		}

		return $options;
	}
}
