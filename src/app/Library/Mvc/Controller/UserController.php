<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use MaiVu\Hummingbird\Lib\Helper\Config;
use MaiVu\Hummingbird\Lib\Helper\State;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Helper\Date;
use MaiVu\Hummingbird\Lib\Helper\Form;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\Mail;
use MaiVu\Hummingbird\Lib\Mvc\Model\User as UserModel;
use Exception;

class UserController extends ControllerBase
{
	public function accountAction()
	{
		$user = User::getInstance();

		if ($user->isGuest())
		{
			$this->view->setVar('registerData', $this->persistent->get('user.register.data', []));
			$this->view->pick('User/Account');
		}
		else
		{
			$this->view->setVar('user', $user);
			$this->view->pick('User/Logout');
		}
	}

	public function forgotAction()
	{
		$this->view->pick('User/Forgot');
	}

	public function requestAction()
	{
		if ($this->request->isPost()
			&& Form::checkToken()
		)
		{
			$type   = $this->request->getPost('requestType', ['trim'], 'P');
			$email  = filter_var($this->request->getPost('email', ['string'], ''), FILTER_VALIDATE_EMAIL);
			$params = [
				'conditions' => 'email = :email: AND active = :yes:',
				'bind'       => [
					'email' => $email,
					'yes'   => 'Y',
				],
			];

			if (!empty($email)
				&& ($user = UserModel::findFirst($params))
				&& $user->save(['token' => sha1($user->username . ':' . $user->password)])
			)
			{
				$siteName = Config::get('siteName');

				if ('P' === $type)
				{
					// Send reset password
					$user->save(['token' => sha1($user->username . ':' . $user->password)]);
					$link    = Uri::getInstance(['uri' => 'user/reset/' . $user->token])->toString(false, true);
					$subject = Text::_('user-reset-request-subject', ['siteName' => $siteName]);
					$body    = Text::_('user-reset-request-body', ['siteName' => $siteName, 'name' => $user->name, 'link' => $link]);
					Mail::sendMail($user->email, $subject, str_replace('\n', PHP_EOL, $body));
					$this->view->setVars(
						[
							'title'   => Text::_('user-request-completed-title', ['email' => $user->email]),
							'message' => Text::_('user-request-completed-msg', ['siteName' => $siteName, 'email' => $user->email]),
						]
					);

					$this->persistent->set('user.token.' . $user->token, true);
				}
				else
				{
					// Send remind username
					$subject = Text::_('username-remind-request-subject', ['siteName' => $siteName]);
					$body    = Text::_('username-remind-request-body', ['siteName' => $siteName, 'name' => $user->name, 'username' => $user->username]);
					Mail::sendMail($user->email, $subject, str_replace('\n', PHP_EOL, $body));
					$this->view->setVars(
						[
							'title'   => Text::_('username-remind-completed-title', ['email' => $user->email]),
							'message' => Text::_('username-remind-completed-msg', ['siteName' => $siteName, 'email' => $user->email]),
						]
					);
				}

				$this->view->pick('User/Completed');
			}
		}
		else
		{
			return $this->response->redirect(Uri::route('user/forgot'), true);
		}
	}

	public function loginAction()
	{
		if (($forward = $this->request->get('forward'))
			&& ($uri = Uri::fromUrl($forward))
			&& $uri->isInternal()
		)
		{
			$redirect = $forward;
		}
		else
		{
			$redirect = Uri::route('user/account');
		}

		if (!User::getInstance()->isGuest())
		{
			return $this->response->redirect($redirect, true);
		}

		if ($this->request->isPost())
		{
			if (!Form::checkToken())
			{
				return $this->response->redirect($redirect, true);
			}

			$username = $this->request->getPost('username');
			$password = $this->request->getPost('password');
			$entity   = UserModel::findFirst(
				[
					'conditions' => 'username = :username: AND active = :yes:',
					'bind'       => [
						'username' => $username,
						'yes'      => 'Y',
					],
				]
			);

			if (!$entity || !$this->security->checkHash($password, $entity->password))
			{
				$this->flashSession->error(Text::_('login-fail-message'));

				return $this->response->redirect(Uri::route('user/account', true), true);
			}

			/** @var UserModel $entity */
			// Update latest visit date
			$entity->assign(['lastVisitedDate' => Date::getInstance()->toSql()])->save();
			User::getInstance($entity)->setActive();
		}

		return $this->response->redirect($redirect, true);
	}

	public function logoutAction()
	{
		$user = User::getInstance();

		if ($user->isGuest()
			|| !$this->request->isPost()
			|| !Form::checkToken()
		)
		{
			$this->flash->error(Text::_('access-denied'));

			return $this->response->redirect(Uri::route(), true);
		}

		if ($this->cookies->has('cms.site.language'))
		{
			$this->cookies->delete('cms.site.language');
		}

		if ($this->cookies->has('cms.user.remember'))
		{
			$this->cookies->delete('cms.user.remember');
		}

		$user->logout();

		return $this->response->redirect(Uri::route('user/account'), true);
	}

	public function registerAction()
	{
		if ($this->request->isGet()
			|| !Form::checkToken()
			|| 'Y' !== Config::get('allowUserRegistration')
		)
		{
			return $this->accountAction();
		}

		$postData  = $this->request->getPost();
		$validData = [];
		$errorMsg  = [];
		$fields    = [
			'name'            => Text::_('your-name'),
			'username'        => Text::_('username'),
			'email'           => Text::_('email'),
			'password'        => Text::_('password'),
			'confirmPassword' => Text::_('confirm-password'),
		];

		foreach ($fields as $name => $text)
		{
			$validData[$name] = trim($postData[$name]);

			if (empty($postData[$name]))
			{
				$errorMsg[] = Text::_('required-field-msg', ['field' => $text]);
			}
		}

		$validData['email'] = filter_var($validData['email'], FILTER_VALIDATE_EMAIL);

		if (false === $validData['email'])
		{
			$errorMsg[] = Text::_('invalid-email-msg');
		}

		$test = User::validatePassword($validData['password']);

		if ($test instanceof Exception)
		{
			$errorMsg[] = $test->getMessage();
		}

		if ($validData['password'] !== $validData['confirmPassword'])
		{
			$errorMsg[] = Text::_('password-not-match');
		}

		if (!empty($validData['username']) || !empty($validData['email']))
		{
			if (!empty($validData['username']))
			{
				$userExists = UserModel::findFirst(
					[
						'conditions' => 'username = :username:',
						'bind'       => [
							'username' => $validData['username'],
						],
					]
				);

				if ($userExists)
				{
					$errorMsg[] = Text::_('the-username-existed-msg', ['username' => $validData['username']]);
				}
			}

			if (!empty($validData['email']))
			{
				$userExists = UserModel::findFirst(
					[
						'conditions' => 'email = :email:',
						'bind'       => [
							'email' => $validData['email'],
						],
					]
				);

				if ($userExists)
				{
					$errorMsg[] = Text::_('the-user-email-existed-msg', ['email' => $validData['email']]);
				}
			}
		}

		// A = auto activate, E = by email, N = by admin
		$newUserActivation = Config::get('newUserActivation', 'E');
		$completed         = false;

		if (empty($errorMsg))
		{
			// Start register new user
			State::setMark('user.registering', true);
			$userEntity           = new UserModel;
			$userEntity->id       = 0;
			$userEntity->name     = $validData['name'];
			$userEntity->username = $validData['username'];
			$userEntity->email    = $validData['email'];
			$userEntity->password = $this->security->hash($validData['password']);
			$userEntity->role     = 'R';
			$userEntity->active   = 'A' === $newUserActivation ? 'Y' : 'N';
			$userEntity->token    = 'E' === $newUserActivation ? sha1($userEntity->username . ':' . $userEntity->password) : null;
			$userEntity->params   = [
				'timezone' => Config::get('timezone', 'UTC'),
				'avatar'   => '',
			];

			if ($userEntity->save())
			{
				$siteName = Config::get('siteName');

				if ('E' === $newUserActivation)
				{
					$link    = Uri::getInstance(['uri' => 'user/activate/' . $userEntity->token])->toString(true, true);
					$subject = Text::_('activate-email-subject', ['username' => $userEntity->username, 'siteName' => $siteName]);
					$body    = Text::_('activate-email-body', ['name' => $userEntity->name, 'siteName' => $siteName, 'link' => $link]);
					Mail::sendMail($userEntity->email, $subject, str_replace('\n', PHP_EOL, $body));
				}

				$mailToAdmin = Config::get('mailToAdminWhenNewUser', 'Y') === 'Y';
				$adminEmail  = filter_var(Config::get('adminEmail', ''), FILTER_VALIDATE_EMAIL);

				if ($mailToAdmin
					&& !empty($adminEmail)
					&& ($adminEmail = filter_var($adminEmail, FILTER_VALIDATE_EMAIL))
				)
				{
					$subject = Text::_('activate-email-subject', ['username' => $userEntity->username, 'siteName' => $siteName]);
					$body    = Text::_('email-notification-new-user-body', ['name' => $userEntity->name, 'username' => $userEntity->username, 'siteName' => $siteName]);
					Mail::sendMail($adminEmail, $subject, str_replace('\n', PHP_EOL, $body));
				}
			}
			else
			{
				$errorMsg[] = Text::_('user-register-failure-msg');
			}

			// Check errors again
			$errorMsg = array_unique($errorMsg);

			if (empty($errorMsg))
			{
				$completed = true;

				switch ($newUserActivation)
				{
					case 'A':
						$this->view->setVar('message', Text::_('user-register-success-msg'));
						break;

					case 'E':
						$this->view->setVar('message', Text::_('user-activate-by-email-msg'));
						break;

					case 'N':
						$this->view->setVar('message', Text::_('user-activate-by-admin-msg'));
						break;
				}

				$this->persistent->remove('user.register.data');
				$this->view->setVar('title', Text::_('user-register-completed-msg', ['name' => $userEntity->name]));
				$this->view->pick('User/Completed');
			}
		}

		if (!$completed)
		{
			$this->persistent->set('user.register.data', $postData);
			$this->flashSession->warning(implode('<br/>', $errorMsg));

			return $this->accountAction();
		}
	}

	public function activateAction()
	{
		$token  = $this->dispatcher->getParam('token', ['alphanum'], '');
		$params = [
			'conditions' => 'token = :token: AND active = :no:',
			'bind'       => [
				'token' => $token,
				'no'    => 'N',
			],
		];

		if (empty($token)
			|| strlen($token) !== 40
			|| !($user = UserModel::findFirst($params))
		)
		{
			return $this->response->redirect(Uri::route(), true);
		}

		$user->token  = null;
		$user->active = 'Y';

		if ($user->save())
		{
			$this->view->setVars(
				[
					'title'   => Text::_('user-activate-completed-msg', ['name' => $user->name]),
					'message' => Text::_('user-activate-success-msg'),
				]
			);

			$this->view->pick('User/Completed');
		}
		else
		{
			return $this->dispatcher->forward(
				[
					'controller' => 'error',
					'action'     => 'show',
				]
			);
		}
	}

	public function resetAction()
	{
		$token  = $this->dispatcher->getParam('token', ['alphanum'], '');
		$params = [
			'conditions' => 'token = :token: AND active = :yes:',
			'bind'       => [
				'token' => $token,
				'yes'   => 'Y',
			],
		];

		if (empty($token)
			|| strlen($token) !== 40
			|| !($user = UserModel::findFirst($params))
		)
		{
			return $this->response->redirect(Uri::route('user/forgot'), true);
		}

		if (!$this->persistent->has('user.token.' . $token))
		{
			$user->token = null;
			$user->save();

			return $this->response->redirect(Uri::route('user/forgot'), true);
		}

		if ($this->request->isGet())
		{
			$this->view->setVar('token', $token);
			$this->view->pick('User/Reset');
		}
		elseif ($this->request->isPost()
			&& Form::checkToken()
		)
		{
			$password1 = $this->request->getPost('password', ['string'], '');
			$password2 = $this->request->getPost('confirmPassword', ['string'], '');
			$test      = User::validatePassword($password1);

			if ($test instanceof Exception)
			{
				$this->flashSession->warning($test->getMessage());

				return $this->response->redirect(Uri::route('user/reset/' . $token), true);
			}

			if (empty($password1)
				|| $password1 !== $password2
			)
			{
				$this->flashSession->warning(Text::_('pwd-empty-or-not-match-msg'));

				return $this->response->redirect(Uri::route('user/reset/' . $token), true);
			}

			$user->token    = null;
			$user->password = $this->security->hash($password1);

			if ($user->save())
			{
				$this->persistent->remove('user.token.' . $token);
				$this->flashSession->success(Text::_('update-password-success-msg'));

				return $this->response->redirect(Uri::route('user/account'), true);
			}

			$this->flashSession->error(Text::_('update-password-failure-msg'));

			return $this->response->redirect(Uri::route('user/reset/' . $token), true);
		}
		else
		{
			return $this->response->redirect(Uri::route('user/forgot'), true);
		}
	}
}