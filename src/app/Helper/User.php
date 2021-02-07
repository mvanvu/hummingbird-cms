<?php

namespace App\Helper;

use App\Mvc\Model\User as UserModel;
use DateTimeZone;
use Exception;
use MaiVu\Php\Registry;

/**
 * Hummingbird User Helper Class
 * @since  1.0-beta1
 * @method   static boolean is() is($keyword)
 * @method   static integer diff() diff($user)
 * @method   static boolean authorise() authorise($coreAction)
 * @method   static boolean isRoot() isRoot()
 * @method   static integer id()
 * @method   static string name()
 * @method   static string email()
 * @method   static string username()
 * @method   static string timezone()
 */
class User
{
	/**
	 * @var array
	 */
	private static $users = [];

	/**
	 * @var User | null
	 */
	private static $activeUser = null;

	/**
	 * @var UserModel
	 */
	private $entity;


	/**
	 * @var Registry
	 */
	private $params;

	private function __construct()
	{

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

	public static function loginWithToken($token)
	{
		if (empty($token) || strlen($token) !== 32)
		{
			return false;
		}

		$entity = UserModel::findFirst(
			[
				'conditions' => 'MD5(secret) = :token: AND active = :yes:',
				'bind'       => [
					'token' => $token,
					'yes'   => 'Y',
				],
			]
		);

		if (!$entity)
		{
			return false;
		}

		$entity->assign(['lastVisitedDate' => Date::now('UTC')->toSql()])->save();

		return User::getInstance($entity)->setActive();
	}

	public function setActive()
	{
		static::$activeUser = null; // Reset active user
		State::set('user.id', $this->entity->id);
		Event::trigger('onUserLoggedIn', [$this], ['Cms']);

		return $this;
	}

	/**
	 * @param null|int|UserModel $identity
	 *
	 * @return User
	 */

	public static function getInstance($identity = null)
	{
		$key = null === $identity ? 'CURRENT' : $identity;

		if ('CURRENT' === $key)
		{
			static $autoLoginCheck = false;
			static $triggerEvent = false;
			$user = static::getActive();

			if (!$autoLoginCheck)
			{
				$autoLoginCheck = true;

				if ($user->is('guest')
					&& Uri::isClient('site')
					&& strlen($userMd5 = Cookie::get(static::getRememberToken(), '')) === 32
					&& ($userEntity = UserModel::findFirst(['conditions' => 'active = :active: AND MD5(username) = :userMd5:', 'bind' => ['active' => 'Y', 'userMd5' => $userMd5]]))
				)
				{
					$user = static::getInstance($userEntity)->setActive();
				}
			}

			if (!$triggerEvent)
			{
				$triggerEvent = true;

				if (!$user->is('guest'))
				{
					Event::trigger('onAfterGuard', [$user], ['Cms']);
				}
			}

			return $user;
		}

		if ($identity instanceof UserModel)
		{
			$id     = (int) $identity->id;
			$entity = $identity;
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

			$user->setParams($entity->params);
			$user->setEntity($entity);
			static::$users[$id] = $user;
		}

		return static::$users[$id];
	}

	public static function getActive()
	{
		if (null === static::$activeUser)
		{
			$identity = 0;
			$userId   = (int) State::get('user.id', 0);

			if ($userId > 0 && $entity = UserModel::findFirst('active = \'Y\' AND id = ' . $userId))
			{
				$identity = $entity;
			}

			static::$activeUser = static::getInstance($identity);
		}

		return static::$activeUser;
	}

	public static function getRememberToken()
	{
		return md5(Uri::getHost() . ':' . Service::request()->getUserAgent());
	}

	public static function login($username, $password, $remember = false)
	{
		if (empty($username) || empty($password))
		{
			return false;
		}

		$time      = time();
		$tempKey   = 'user.login.count';
		$tempValue = ['count' => 0, 'time' => $time];
		$temp      = State::get($tempKey, $tempValue, false);

		if ($temp['count'] > 4)
		{
			// Retry login after 60 seconds
			$tempTime = $temp['time'] + 60;

			if ($time < $tempTime)
			{
				$seconds = $tempTime - $time;
				Service::flashSession()->warning('<span class="js-count-down">' . Text::plural('retry-login-after-seconds', $seconds, ['seconds' => $seconds]) . '</span>');

				return false;
			}

			// Reset duration
			$temp = $tempValue;
			State::set($tempKey, $temp);
		}

		$entity = UserModel::findFirst(
			[
				'conditions' => 'username = :username: AND active = :yes:',
				'bind'       => [
					'username' => $username,
					'yes'      => 'Y',
				],
			]
		);

		if (!$entity || !Service::security()->checkHash($password, $entity->password))
		{
			$temp['count']++;
			$temp['time'] = $time;
			State::set($tempKey, $temp);

			return false;
		}

		State::remove($tempKey);

		if ($remember)
		{
			Cookie::set(static::getRememberToken(), md5($username));
		}

		$entity->assign(['lastVisitedDate' => Date::now('UTC')->toSql()])->save();

		return User::getInstance($entity)->setActive();
	}

	public static function forward403()
	{
		Service::dispatcher()->forward(
			[
				'controller' => Uri::isClient('site') ? 'error' : 'admin_error',
				'action'     => 'show',
				'params'     => [
					'code'    => 403,
					'title'   => Text::_('403-title'),
					'message' => Text::_('403-message'),
				],
			]
		);
	}

	public static function __callStatic($name, $arguments)
	{
		$user = User::getActive();

		switch ($name)
		{
			case 'id':
			case 'username':
			case 'name':
			case 'email':
				return $user->getEntity()->{$name};

			case 'is':
			case 'isRoot':
			case 'authorise':
			case 'diff':
				return $user->getEntity()->{$name}(...$arguments);

			case 'timezone':
				return $user->getTimezone();
		}

		trigger_error('PHP Fatal Error Call to undefined method: ' . $name . '()');
	}

	public function getEntity()
	{
		return $this->entity;
	}

	public function setEntity(UserModel $entity)
	{
		$this->entity = $entity;

		return $this;
	}

	public function getTimezone()
	{
		return new DateTimeZone($this->getParams()->get('timezone', Config::get('timezone')));
	}

	public function getParams()
	{
		return $this->params;
	}

	public function setParams($params)
	{
		$this->params = new Registry($params);
	}

	public function forwardLogin()
	{
		Service::dispatcher()->forward(
			[
				'controller' => Uri::isClient('site') ? 'user' : 'admin_user',
				'action'     => 'account',
				'params'     => [
					'forward' => Uri::fromServer(),
				],
			]
		);
	}

	public function logout()
	{
		State::remove('user.id');
		Cookie::remove(static::getRememberToken());
		Event::trigger('onUserLoggedOut', [$this], ['Cms']);
		$id = (int) $this->entity->id;

		if (isset(static::$users[$id]))
		{
			unset(static::$users[$id]);
		}

		return $this;
	}

	public function getAvatar()
	{
		if ($avatar = $this->getParams()->get('avatar'))
		{
			return ROOT_URI . '/upload/' . $avatar;
		}

		return null;
	}

	public function __get($name)
	{
		return $this->getEntity()->{$name};
	}

	public function __call($name, $arguments)
	{
		if (is_callable([$this->entity, $name]))
		{
			return call_user_func_array([$this->entity, $name], $arguments);
		}

		trigger_error('PHP Fatal Error Call to undefined method: ' . $name . '()');
	}
}
