<?php

namespace App\Mvc\Controller;

use App\Helper\Assets;
use App\Helper\Config;
use App\Helper\Database;
use App\Helper\Event;
use App\Helper\Queue;
use App\Helper\ReCaptcha;
use App\Helper\Service;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\User;
use App\Mvc\Model\User as UserModel;
use App\Queue\SendMail;
use App\Traits\User as UserTrait;
use Exception;

class UserController extends ControllerBase
{
	use UserTrait;

	public function forgotAction()
	{
		$this->view->pick('User/Forgot');
	}

	public function requestAction()
	{
		if ($this->request->isPost())
		{
			$this->handleRequestAction();
		}
		else
		{
			return Uri::redirect(Uri::route('user/forgot'));
		}
	}

	public function loginAction()
	{
		$hasForward = false;

		if (($forward = $this->request->get('forward'))
			&& ($uri = Uri::fromUrl($forward))
			&& $uri->isInternal()
		)
		{
			$redirect   = $forward;
			$hasForward = true;
		}
		elseif ($forward = $this->dispatcher->getParam('forward'))
		{
			$redirect   = $forward;
			$hasForward = true;
		}
		else
		{
			$redirect = Uri::back(Uri::route('user/account'), false);
		}

		if (!User::is('guest'))
		{
			return Uri::redirect($redirect);
		}

		if ($this->request->isPost())
		{
			$username = $this->request->getPost('username');
			$password = $this->request->getPost('password');
			$remember = $this->request->getPost('remember');

			if (!User::login($username, $password, 'Y' === $remember))
			{
				$this->flashSession->error(Text::_('login-fail-message'));

				return Uri::redirect(Uri::route('user/account', $hasForward ? ['forward' => $forward] : false));
			}
		}

		return Uri::redirect($redirect);
	}

	public function logoutAction()
	{
		$user = User::getActive();

		if ($user->is('guest') || !$this->request->isPost())
		{
			$this->flash->error(Text::_('access-denied'));

			return Uri::redirect(Uri::route());
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

		return Uri::redirect(Uri::route('user/account'));
	}

	public function registerAction()
	{
		if (!ReCaptcha::isValid(true))
		{
			Uri::back();
		}

		$postData     = $this->request->getPost();
		$responseData = $this->handleUserRegister($postData);

		if (false === $responseData)
		{
			$this->accountAction();
		}
		elseif ($responseData['success'])
		{
			$this->persistent->remove('user.register.data');
			$this->view->setVar('title', $responseData['titleMessage']);

			if ($responseData['bodyMessage'])
			{
				$this->view->setVar('message', $responseData['bodyMessage']);
			}

			$this->view->pick('User/Completed');
		}
		else
		{
			$this->persistent->set('user.register.data', $postData);
			$this->flashSession->warning(implode('<br/>', $responseData['errorMessages']));
			$this->accountAction();
		}
	}

	public function accountAction()
	{
		$user = User::getActive();
		$this->view->setVar('forward', $this->dispatcher->getParam('forward') ?: null);

		if ($user->is('guest'))
		{
			Assets::add('js/mini-query.validate.js');
			$this->view->setVar('registerData', $this->persistent->get('user.register.data', []));
			$this->view->pick('User/Account');
		}
		else
		{
			$this->view->setVar('user', $user);
			$this->view->pick('User/Logout');
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
			return Uri::redirect(Uri::route('user/account'));
		}

		if (Service::db()->update(Database::table('users'), ['token', 'active'], [null, 'Y'], 'id = ' . (int) $user->id))
		{
			$this->view->setVars(
				[
					'title'   => Text::_('user-activate-completed-msg', ['name' => $user->name]),
					'message' => Text::_('user-activate-success-msg'),
				]
			);

			$this->view->pick('User/Completed');
			Event::trigger('onUserAfterActivation', [$user], ['Cms']);
		}
		else
		{
			$this->dispatcher->forward(
				[
					'controller' => 'error',
					'action'     => 'show',
				]
			);
		}
	}

	public function resetAction()
	{
		$token = $this->dispatcher->getParam('token', ['string']);
		$api   = false;

		if (strpos($token, 'api-') === 0)
		{
			$token = substr($token, 4);
			$api   = true;
		}

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
			return Uri::redirect(Uri::route('user/forgot'));
		}

		if ($api)
		{
			$this->persistent->set('user.token.' . $token, true);
		}

		if (!$this->persistent->has('user.token.' . $token))
		{
			$user->token = null;
			$user->save();

			return Uri::redirect(Uri::route('user/forgot'));
		}

		if ($this->request->isGet())
		{
			$this->view->setVar('token', $token);
			$this->view->pick('User/Reset');
		}
		elseif ($this->request->isPost())
		{
			$password1 = $this->request->getPost('password', ['string'], '');
			$password2 = $this->request->getPost('confirmPassword', ['string'], '');
			$test      = User::validatePassword($password1);

			if ($test instanceof Exception)
			{
				$this->flashSession->warning($test->getMessage());

				return Uri::redirect(Uri::route('user/reset/' . $token));
			}

			if (empty($password1)
				|| $password1 !== $password2
			)
			{
				$this->flashSession->warning(Text::_('pwd-empty-or-not-match-msg'));

				return Uri::redirect(Uri::route('user/reset/' . $token));
			}

			$user->token    = null;
			$user->password = $this->security->hash($password1);

			if ($user->save())
			{
				$this->persistent->remove('user.token.' . $token);
				$this->flashSession->success(Text::_('update-password-success-msg'));

				return Uri::redirect(Uri::route('user/account'));
			}

			$this->flashSession->error(Text::_('update-password-failure-msg'));

			return Uri::redirect(Uri::route('user/reset/' . $token));
		}
		else
		{
			return Uri::redirect(Uri::route('user/forgot'));
		}
	}
}