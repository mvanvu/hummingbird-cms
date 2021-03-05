<?php

namespace App\Form\Field;

use App\Mvc\Model\User;
use MaiVu\Php\Form\Field\Select;

class CmsUser extends Select
{
	public function getOptions()
	{
		$options = parent::getOptions();

		foreach (User::find('active = \'Y\'') as $user)
		{
			$options[] = [
				'value' => $user->id,
				'text'  => $user->name,
			];
		}

		return parent::parseOptions($options);
	}
}