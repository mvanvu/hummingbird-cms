<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use Phalcon\Security;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\User as CmsUser;
use MaiVu\Hummingbird\Lib\Helper\State;
use MaiVu\Hummingbird\Lib\Form\Form;
use MaiVu\Hummingbird\Lib\Factory;
use Exception;

class User extends ModelBase
{

	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var string
	 */
	public $name;

	/**
	 *
	 * @var string
	 */
	public $email;

	/**
	 *
	 * @var string
	 */
	public $username;

	/**
	 *
	 * @var string
	 */
	public $password;

	/**
	 *
	 * @var string
	 */
	public $role;


	/**
	 *
	 * @var string
	 */
	public $active;

	/**
	 *
	 * @var string
	 */
	public $lastVisitedDate;

	/**
	 *
	 * @var string
	 */
	public $createdAt;

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
	public $params;

	/**
	 *
	 * @var string
	 */
	public $secret;

	/**
	 *
	 * @var string
	 */
	public $token;

	/**
	 * @var string
	 */

	protected $titleField = 'name';

	/**
	 * @var boolean
	 */

	protected $standardMetadata = true;

	/**
	 * Validations and business logic
	 *
	 * @return boolean
	 */
	public function validation()
	{
		foreach (['username', 'email'] as $field)
		{
			$result = parent::findFirst([
				'conditions' => $field . ' = :' . $field . ':',
				'bind'       => [
					$field => $this->{$field},
				],
			]);

			if ($result
				&& (!$this->id || $result->id != $this->id)
			)
			{
				Factory::getService('flashSession')->warning(Text::_($field . '-exists-message'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('users');

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

	public function getParamsFormsManager()
	{
		$paramsFormsManager = parent::getParamsFormsManager();
		$paramsFormsManager->set('params', new Form('FormData.params', __DIR__ . '/Form/User/Param.php'));

		return $paramsFormsManager;
	}

	public function delete(): bool
	{
		$user         = CmsUser::getInstance();
		$flashSession = Factory::getService('flashSession');

		if ($this->isRoot())
		{
			$flashSession->error(Text::_('delete-root-user-msg', ['username' => $this->username]));

			return false;
		}

		if ($user->getEntity()->id == $this->id)
		{
			$flashSession->error(Text::_('delete-yourself-msg', ['username' => $this->username]));

			return false;
		}

		return parent::delete();
	}

	public function beforeSave()
	{
		parent::beforeSave();
		$user = CmsUser::getInstance();

		if (true === State::getMark('user.registering', false)
			|| $user->access('super')
		)
		{
			return true;
		}

		if (($this->id == $user->id && $this->role != $user->role)
			|| ($this->role == 'S' && !$user->access('super'))
		)
		{
			return false;
		}
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

		if ($result = parent::save())
		{
			$user = CmsUser::getInstance();

			if (!$user->isGuest() && $user->id == $this->id)
			{
				$user->setParams($this->params);
			}
		}

		return $result;
	}

	public function isRoot()
	{
		return $this->secret && $this->secret === Factory::getConfig()->get('SECRET.ROOT_KEY');
	}

	public function controllerBeforeBindData(&$rawData)
	{
		State::setMark('user.currentPassword', $this->password);
	}

	public function controllerDoBeforeSave(&$validData)
	{
		$security = Factory::getService('security');

		if (empty($validData['password']))
		{
			$this->password = State::getMark('user.currentPassword');
		}
		else
		{
			$this->password = $security->hash($validData['password']);
		}

		if (empty($this->password))
		{
			throw new Exception(Text::_('password-required-msg'));
		}

		$validData['password'] = $validData['confirmPassword'] = $this->password;
	}
}