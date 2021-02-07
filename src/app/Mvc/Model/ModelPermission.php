<?php

namespace App\Mvc\Model;

use App\Helper\User as Auth;
use Phalcon\Mvc\Model as PhalconModel;

class ModelPermission extends PhalconModel
{
	/**
	 * @var string | null
	 */
	public $permitPkgName = null;

	public function canCreate($forward403 = false): bool
	{
		return $this->authorize('create', $forward403);
	}

	public function authorize($action, $forward403 = false): bool
	{
		if (empty($this->permitPkgName))
		{
			$className           = explode('\\', get_class($this));
			$this->permitPkgName = lcfirst(array_pop($className));
		}

		$user    = Auth::getActive();
		$isOwner = property_exists($this, 'createdBy') && $this->createdBy == $user->id;

		if ($user->authorise($this->permitPkgName . '.' . $action)
			|| ($isOwner && $user->authorise($this->permitPkgName . '.manageOwn'))
		)
		{
			return true;
		}

		$forward403 && Auth::forward403();

		return false;
	}

	public function canManage($forward403 = false): bool
	{
		return $this->authorize('manage', $forward403);
	}

	public function canEditState($forward403 = false): bool
	{
		return $this->authorize('editState', $forward403);
	}

	public function canEdit($forward403 = false): bool
	{
		return $this->authorize('edit', $forward403);
	}

	public function canDelete($forward403 = false): bool
	{
		return $this->authorize('delete', $forward403);
	}

	public function canUnlock()
	{
		return property_exists($this, 'checkedBy')
			&& ($this->checkedBy == Auth::id()) || ($this instanceof User && 1 === Auth::diff($this));
	}
}