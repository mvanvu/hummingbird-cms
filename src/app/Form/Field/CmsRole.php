<?php

namespace App\Form\Field;

use App\Helper\User;
use App\Mvc\Model\Role;
use MaiVu\Php\Form\Field\Select;

class CmsRole extends Select
{
	protected $value = 3; // Registered role

	public function getOptions()
	{
		static $options = null;

		if (null === $options)
		{
			$user    = User::getActive();
			$options = [];

			if (!$user->is('guest'))
			{
				$params = ['order' => 'name ASC'];

				if ($user->is('manager') && !$user->is('super'))
				{
					$params['conditions'] = 'type = \'R\'';
				}

				foreach (Role::find($params) as $role)
				{
					$options[] = [
						'value' => $role->id,
						'text'  => $role->name,
					];
				}
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
