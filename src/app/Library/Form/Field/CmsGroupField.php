<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Mvc\Model\UcmGroupField;
use MaiVu\Hummingbird\Lib\Factory;

class CmsGroupField extends Select
{
	protected $context = null;

	public function getOptions()
	{
		$options = parent::getOptions();
		$context = $this->context ?: Factory::getService('dispatcher')->getParam('context');

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
