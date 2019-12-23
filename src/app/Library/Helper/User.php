<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use MaiVu\Hummingbird\Lib\Mvc\Model\User as UserModel;
use MaiVu\Php\Registry;
use DateTimeZone, Exception;

class User
{
	/** @var array */
	private static $users = [];

	/** @var UserModel */
	private $entity;

	/** @var UserModel */
	private $isRoot = false;

	/** @var array */
	private $accessList = [];

	/** @var Registry */
	private $params;

	/**
	 * @param   null|int|UserModel  $identity
	 *
	 * @return User
	 */

	public static function getInstance($identity = null)
	{
		$key = null === $identity ? 'CURRENT' : $identity;

		if ('CURRENT' === $key)
		{
			if (isset(static::$users[$key]))
			{
				return static::$users[$key];
			}

			$user = static::getActive();
			$id   = 0;

			if (($user instanceof User)
				&& $user->getEntity()->active === 'Y'
			)
			{
				static::$users[$key] = $user;

				return static::$users[$key];
			}
		}
		elseif ($identity instanceof UserModel)
		{
			$id = (int) $identity->id;
		}
		else
		{
			$id = (int) $identity;
		}

		if (!isset(static::$users[$id]))
		{
			if (!isset($entity) && $id > 0)
			{
				$entity = UserModel::findFirst(
					[
						'conditions' => 'id = :id: AND active = :active:',
						'bind'       => [
							'id'     => $id,
							'active' => 'Y',
						],
					]
				);
			}

			$user = new User;

			if (empty($entity))
			{
				$entity = new UserModel;
			}
			else
			{
				switch ($entity->role)
				{
					case 'R':
						$user->accessList[] = 'register';
						break;

					case 'A':
						$user->accessList[] = 'register';
						$user->accessList[] = 'author';
						break;

					case 'M':
						$user->accessList[] = 'register';
						$user->accessList[] = 'author';
						$user->accessList[] = 'manager';
						break;

					case 'S':
						$user->accessList[] = 'register';
						$user->accessList[] = 'author';
						$user->accessList[] = 'manager';
						$user->accessList[] = 'super';
						break;
				}
			}

			if ($entity->isRoot())
			{
				$user->isRoot = true;
			}

			$user->setParams($entity->params);
			$user->setEntity($entity);
			static::$users[$id] = $user;
		}

		return static::$users[$id];
	}

	private function __construct()
	{

	}

	public static function getActive()
	{
		return State::get('user');
	}

	public function setActive()
	{
		State::set('user', $this);

		return $this;
	}

	public function logout()
	{
		State::remove('user');
		$id = (int) $this->entity->id;

		if (isset(static::$users[$id]))
		{
			unset(static::$users[$id]);
		}

		return $this;
	}

	public function setEntity(UserModel $entity)
	{
		$this->entity = $entity;

		return $this;
	}

	public function access($accessName)
	{
		return ($this->isRoot || in_array($accessName, $this->accessList, true));
	}

	public function isRole($accessName)
	{
		$role     = $this->entity->role;
		$rolesMap = [
			'A' => 'author',
			'R' => 'register',
			'M' => 'manager',
			'S' => 'super',
		];

		return isset($rolesMap[$role]) && $rolesMap[$role] === $accessName;
	}

	public function setParams($params)
	{
		$this->params = new Registry($params);
	}

	public function getParams()
	{
		return $this->params;
	}

	public function getTimezone()
	{
		return new DateTimeZone($this->getParams()->get('timezone', Config::get('timezone')));
	}

	public function getEntity()
	{
		return $this->entity;
	}

	public function isGuest()
	{
		return (int) $this->entity->id < 1;
	}

	public function isRoot()
	{
		return $this->isRoot;
	}

	public function __get($name)
	{
		return $this->getEntity()->{$name};
	}

	public static function validatePassword($password)
	{
		$minimumLength = 6;
		$maximumLength = 100;
		$len           = strlen($password);

		if ($len < $minimumLength)
		{
			return new Exception(Text::_('password-too-short-msg'));
		}

		if ($len > $maximumLength)
		{
			return new Exception(Text::_('password-too-long-msg'));
		}

		if (preg_match('/\s+/', $password))
		{
			return new Exception(Text::_('password-has-spaces-msg'));
		}

		return true;
	}
}
