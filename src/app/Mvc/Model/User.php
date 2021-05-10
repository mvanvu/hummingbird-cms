<?php

namespace App\Mvc\Model;

use App\Factory\Factory;
use App\Helper\Service;
use App\Helper\State;
use App\Helper\Text;
use App\Helper\User as Auth;
use MaiVu\Php\Registry;
use Phalcon\Security;

class User extends ModelBase
{
	/**
	 *
	 * @var integer
	 */
	public $id = 0;

	/**
	 *
	 * @var integer
	 */
	public $roleId = 0;

	/**
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 *
	 * @var string
	 */
	public $email = null;

	/**
	 *
	 * @var string
	 */
	public $username = null;

	/**
	 *
	 * @var string
	 */
	public $password = null;

	/**
	 *
	 * @var string
	 */
	public $active = 'N';

	/**
	 *
	 * @var string
	 */
	public $lastVisitedDate = null;

	/**
	 *
	 * @var string
	 */
	public $createdAt = null;

	/**
	 *
	 * @var integer
	 */
	public $createdBy = 0;

	/**
	 *
	 * @var string
	 */
	public $modifiedAt = null;

	/**
	 *
	 * @var integer
	 */
	public $modifiedBy = 0;

	/**
	 *
	 * @var string
	 */
	public $checkedAt = null;

	/**
	 *
	 * @var integer
	 */
	public $checkedBy = 0;

	/**
	 *
	 * @var string
	 */
	public $params = '{}';

	/**
	 *
	 * @var string
	 */
	public $secret = null;

	/**
	 *
	 * @var string
	 */
	public $token = null;

	/**
	 * @var string
	 */

	protected $titleField = 'name';

	/**
	 * @var boolean
	 */

	protected $standardMetadata = true;

	public function validation()
	{
		foreach (['username', 'email'] as $field)
		{
			$result = parent::findFirst(
				[
					'conditions' => $field . ' = :' . $field . ':',
					'bind'       => [
						$field => $this->{$field},
					],
				]
			);

			if ($result && (!$this->id || $result->id != $this->id))
			{
				Service::flashSession()->warning(Text::_($field . '-exists-message'));

				return false;
			}
		}

		if (($this->isRoot() || (!Auth::is('guest') && Auth::is('self'))) && $this->no('active'))
		{
			Service::flashSession()->warning(Text::_('access-denied'));

			return false;
		}
	}

	public function isRoot()
	{
		return $this->secret && $this->secret === Factory::getConfig()->get('secret.rootKey');
	}

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('users');
		$this->hasOne('roleId', Role::class, 'id', ['reuse' => true, 'alias' => 'role']);

		if ($this->id)
		{
			$this->skipAttributes(['secret']);
		}
	}

	public function getOrderFields()
	{
		return [
			'createdAt',
			'id',
			'name',
			'username',
			'email',
			'active',
		];
	}

	public function getSearchFields()
	{
		return [
			'name',
			'username',
			'email',
		];
	}

	public function delete(): bool
	{
		return $this->canDeleteUser($this, true) ? parent::delete() : false;
	}

	public function canDeleteUser($entity = null, $flashMessage = false): bool
	{
		if (!$entity instanceof User)
		{
			$entity = $entity instanceof Auth ? $entity->getEntity() : $this;
		}

		$user         = Auth::getActive();
		$flashSession = Service::flashSession();

		if ($entity->is('root'))
		{
			$flashMessage && $flashSession->error(Text::_('403-title'));

			return false;
		}

		if ($entity->is('self'))
		{
			$flashMessage && $flashSession->error(Text::_('delete-yourself-msg'));

			return false;
		}

		if (1 !== $user->diff($entity))
		{
			$flashMessage && $flashSession->error(Text::_('403-title'));

			return false;
		}

		return true;
	}

	public function is($keyword, $inOperator = true): bool
	{
		if (is_array($keyword))
		{
			foreach ($keyword as $kw)
			{
				$result = $this->is($kw);

				if ($inOperator)
				{
					if ($result)
					{
						return true;
					}
				}
				elseif (!$result)
				{
					return false;
				}
			}

			return false;
		}

		if (!$this->role && in_array($keyword, ['register', 'manager', 'super']))
		{
			return false;
		}

		switch ($keyword)
		{
			case 'register':
				return in_array($this->role->type, ['R', 'M', 'S']);

			case 'manager':
				return in_array($this->role->type, ['M', 'S']);

			case 'super':
				return $this->role->type === 'S';

			case 'root':
				return $this->isRoot();

			case 'self':
				return Auth::getActive()->id == $this->id;

			case 'guest':
				return (int) $this->id < 1;

			default:
				return false;
		}
	}

	public function diff(User $user): int
	{
		if ($this->roleId == $user->roleId || ($this->is('root') && $user->is('root')))
		{
			return 0;
		}

		foreach (['root', 'super', 'manager'] as $role)
		{
			if ($this->is($role))
			{
				return 1;
			}

			if ($user->is($role))
			{
				return -1;
			}
		}

		return 0;
	}

	public function beforeSave()
	{
		return $this->canSaveUser($this);
	}

	public function canSaveUser($entity = null): bool
	{
		if (!$entity instanceof User)
		{
			$entity = $entity instanceof Auth ? $entity->getEntity() : $this;
		}

		$user        = Auth::getActive();
		$isNew       = empty($entity->id);
		$registering = true === State::getMark('user.registering', false) && $isNew;
		$isSelf      = $user->id == $entity->id && !$isNew;

		if ($registering
			|| $isSelf
			|| ($user->isRoot() && !$entity->isRoot())
			|| 1 === $user->diff($entity)
		)
		{
			if (!is_string($this->params))
			{
				$this->params = $this->params ? json_encode($this->params) : '{}';
			}

			return true;
		}

		return false;
	}

	public function save(): bool
	{
		if (empty($this->secret))
		{
			$security     = new Security;
			$this->secret = $security->getRandom()->uuid();

			while (User::findFirst(
				[
					'conditions' => 'secret = :secret:',
					'bind'       => [
						'secret' => $this->secret,
					],
				]
			))
			{
				$this->secret = $security->getRandom()->uuid();
			}
		}

		if (empty($this->roleId) && ($role = Role::findFirst('type = \'R\'')))
		{
			$this->roleId = (int) $role->id;
		}

		if ($result = parent::save())
		{
			$user = Auth::getActive();

			if (!$user->is('guest') && $user->id == $this->id)
			{
				$user->setParams($this->params);
			}
		}

		return $result;
	}

	public function authorise(string $permission): bool
	{
		static $permissions = [];
		$roleId = (int) $this->roleId;

		if (!isset($permissions[$roleId]))
		{
			$permissions[$roleId] = new Registry;

			if ($this->role)
			{
				foreach (Registry::parseData($this->role->permissions ?: '{}') as $package => $actions)
				{
					$coreAdmin  = ($actions['admin'] ?? 'N') === 'Y';
					$coreManage = ($actions['manage'] ?? 'N') === 'Y';

					foreach ($actions as $action => $value)
					{
						$core = $package . '.' . $action;

						if ($coreAdmin)
						{
							$permissions[$roleId]->set($core, true);
						}
						elseif (!$coreManage)
						{
							$permissions[$roleId]->set($core, false);
						}
						else
						{
							$permissions[$roleId]->set($core, $value === 'Y');
						}
					}
				}
			}
		}

		return $this->is(['root', 'super']) || $permissions[$roleId]->get($permission);
	}

	public function canEdit($forward403 = false): bool
	{
		if ($this->canSaveUser($this))
		{
			return true;
		}

		$forward403 && Auth::forward403();

		return false;
	}

	public function canDelete($forward403 = false): bool
	{
		if ($this->canDeleteUser($this))
		{
			return parent::canDelete($forward403);
		}

		$forward403 && Auth::forward403();

		return false;
	}
}