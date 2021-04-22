<?php

namespace App\Traits;

use App\Factory\Factory;
use App\Helper\Config;
use App\Helper\Date;
use App\Helper\Event;
use App\Helper\Queue;
use App\Helper\Service;
use App\Helper\State;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\User as CMSUser;
use App\Mvc\Model\Role;
use App\Mvc\Model\User as UserModel;
use App\Queue\SendMail;
use Exception;
use Throwable;

trait User
{

	public function handleUserRegister(&$postData = null)
	{
		$request = Service::request();

		if ($request->isGet()
			|| IS_CLI
			|| (IS_CMS && 'Y' !== Config::get('allowUserRegistration', 'N'))
			|| (IS_API && 'Y' !== Config::get('allowUserApiRegistration', 'N'))
		)
		{
			return false;
		}

		if (null === $postData)
		{
			$postData = $request->getPost();
		}

		if (Config::get('userEmailAsUsername', 'Y') === 'Y')
		{
			$postData['username'] = $postData['email'] ?? null;
		}

		$validData = [];
		$errorsMsg = [];
		$fields    = [
			'name'            => Text::_('your-name'),
			'username'        => Text::_('username'),
			'email'           => Text::_('email'),
			'password'        => Text::_('password'),
			'confirmPassword' => Text::_('confirm-password'),
		];

		foreach ($fields as $name => $text)
		{
			$validData[$name] = trim($postData[$name] ?? '');

			if (empty($postData[$name]))
			{
				$errorsMsg[] = Text::_('required-field-msg', ['field' => $text]);
			}

			if ('email' === $name && false === filter_var($validData['email'], FILTER_VALIDATE_EMAIL))
			{
				$errorsMsg[] = Text::_('invalid-email-msg', ['field' => $text]);
			}
		}

		$test = CMSUser::validatePassword($validData['password']);

		if ($test instanceof Exception)
		{
			$errorsMsg[] = $test->getMessage();
		}

		if ($validData['password'] !== $validData['confirmPassword'])
		{
			$errorsMsg[] = Text::_('password-not-match');
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
					$errorsMsg[] = Text::_('the-username-existed-msg', ['username' => $validData['username']]);
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
					$errorsMsg[] = Text::_('the-user-email-existed-msg', ['email' => $validData['email']]);
				}
			}
		}

		// A = auto activate, E = by email, N = by admin
		$newUserActivation = Config::get(IS_CMS ? 'newUserActivation' : 'newUserApiActivation', 'E');
		$responseData      = [
			'success'       => false,
			'errorMessages' => [],
			'titleMessage'  => null,
			'bodyMessage'   => null,
			'userData'      => null,
		];

		try
		{
			if (IS_CMS)
			{
				Event::trigger('onUserBeforeRegister', [&$validData, &$errorsMsg], ['Cms']);
			}
			else
			{
				$apiSecret    = md5(Factory::getConfig()->get('secret.apiKey'));
				$headerSecret = Service::request()->getHeader('HTTP_API_SECRET_KEY');

				if (empty($apiSecret)
					|| empty($headerSecret)
					|| $apiSecret !== $headerSecret
				)
				{
					throw new Exception(Text::_('access-denied'), 403);
				}

				$this->callback('userBeforeRegister', [&$validData, &$errorsMsg]);
			}

		}
		catch (Throwable $e)
		{
			$errorsMsg[] = $e->getMessage();
		}

		if (empty($errorsMsg))
		{
			// Start register new user
			State::setMark('user.registering', true);
			$userEntity            = new UserModel;
			$userEntity->id        = 0;
			$userEntity->name      = $validData['name'];
			$userEntity->username  = $validData['username'];
			$userEntity->email     = $validData['email'];
			$userEntity->createdAt = Date::getInstance('now', 'UTC')->toSql();
			$userEntity->password  = Service::security()->hash($validData['password']);
			$userEntity->roleId    = Role::getDefault()->id;
			$userEntity->active    = 'A' === $newUserActivation ? 'Y' : 'N';
			$userEntity->token     = 'E' === $newUserActivation ? sha1($userEntity->username . ':' . $userEntity->password) : null;
			$userEntity->params    = [
				'timezone' => Config::get('timezone', 'UTC'),
				'avatar'   => '',
			];

			if ($userEntity->save())
			{
				$siteName                 = Config::get('siteName');
				$responseData['userData'] = $userEntity;

				if ('E' === $newUserActivation)
				{
					$link    = Uri::getInstance(['uri' => 'user/activate/' . $userEntity->token])->toString(true, true);
					$subject = Text::_('activate-email-subject', ['username' => $userEntity->username, 'siteName' => $siteName]);
					$body    = Text::_('activate-email-body', ['name' => $userEntity->name, 'siteName' => $siteName, 'link' => $link]);
					$payload = [
						'recipient' => $userEntity->email,
						'subject'   => $subject,
						'body'      => str_replace('\n', PHP_EOL, $body),
					];
					Queue::add(SendMail::class, $payload);
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
					$payload = [
						'recipient' => $adminEmail,
						'subject'   => $subject,
						'body'      => str_replace('\n', PHP_EOL, $body),
					];
					Queue::add(SendMail::class, $payload);
				}

				if (IS_CMS)
				{
					Event::trigger('onUserRegisterFinished', [$userEntity, $validData], ['Cms']);
				}
				else
				{
					$this->callback('userRegisterFinished', [$userEntity, $validData]);
				}
			}
			else
			{
				$errorsMsg[] = Text::_('user-register-failure-msg');
			}

			// Check errors again
			$errorsMsg = array_unique($errorsMsg);

			if (empty($errorsMsg))
			{
				$responseData['success']      = true;
				$responseData['titleMessage'] = Text::_('user-register-completed-msg', ['name' => $userEntity->name]);

				switch ($newUserActivation)
				{
					case 'A':
						$responseData['bodyMessage'] = Text::_('user-register-success-msg');
						break;

					case 'E':
						$responseData['bodyMessage'] = Text::_('user-activate-by-email-msg');
						break;

					case 'N':
						$responseData['bodyMessage'] = Text::_('user-activate-by-admin-msg');
						break;
				}
			}
		}

		$responseData['errorMessages'] = $errorsMsg;

		return $responseData;
	}
}