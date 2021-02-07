<?php

namespace App\Traits;

use App\Factory\ApiApplication;
use App\Helper\Event;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\User;
use App\Mvc\Model\CategoryBase;
use App\Mvc\Model\ModelPermission;
use App\Mvc\Model\UcmComment;
use App\Mvc\Model\User as UserModel;
use Exception;
use Phalcon\Mvc\Dispatcher;

trait Permission
{
	public function beforeExecuteRoute(Dispatcher $dispatcher)
	{
		$user           = $this->user();
		$controllerName = $dispatcher->getControllerName();
		$actionName     = $dispatcher->getActionName();
		$isLoginOut     = in_array($controllerName, ['user', 'admin_user']) && in_array($actionName, ['login', 'logout']);

		if ($isLoginOut || $user->is('super'))
		{
			return true;
		}

		$model      = $this->model ?? null;
		$authorised = $this->isAuthorized();

		if ($authorised && $model instanceof ModelPermission)
		{
			if ($model->canManage())
			{
				if ($model instanceof CategoryBase)
				{
					$authorised = $model->authorize('manageCategory');
				}
				elseif ($model instanceof UcmComment)
				{
					$authorised = $model->authorize('manageComment');
				}
				else
				{
					switch ($actionName)
					{
						case 'copy';
							$authorised = $model->canCreate();
							break;

						case 'edit';
							$authorised = $model->authorize($model->id ? 'edit' : 'create');
							break;

						case 'status';
							$authorised = $model->canEditState();
							break;

						case 'delete';
							$authorised = $model->canDelete();
							break;

						default:
							$actionCallback = $this->permissions[$actionName] ?? null;

							if ($actionCallback && is_callable($actionCallback))
							{
								$authorised = true === call_user_func($actionCallback);
							}
							else
							{
								$authorised = $model->authorize($actionName);
							}

							break;
					}
				}
			}
			else
			{
				$authorised = false;
			}
		}

		if (!$authorised)
		{
			User::forward403();

			return false;
		}

		return true;
	}

	public function user()
	{
		return User::getActive();
	}

	protected function isAuthorized(User $user = null)
	{
		$defaultRole = Uri::isClient('administrator') ? 'manager' : 'register';

		return ($user ?? $this->user())->is($this->role ?? $defaultRole);
	}

	/**
	 * @param ApiApplication $app
	 *
	 * @throws Exception
	 */
	public function onBeforeHandleApi(ApiApplication $app)
	{
		$publicUris = array_map(function ($publicUri) {

			return $this->apiPrefix . '/' . preg_replace('#^' . preg_quote($this->apiPrefix, '#') . '/#', '', $publicUri);

		}, $this->publicUris ?? []);

		if (in_array($this->requestUri, $publicUris))
		{
			return;
		}

		$user = $this->user();

		if ($user->is('guest'))
		{
			$auth   = $app->request->getServer('HTTP_AUTHORIZATION') ?: '';
			$params = [
				'conditions' => 'MD5(secret) = :secret: AND active = :yes:',
				'bind'       => [
					'yes'    => 'Y',
					'secret' => str_replace('Bearer ', '', $auth),
				],
			];

			if (empty($auth)
				|| strpos($auth, 'Bearer ') !== 0
				|| !($entity = UserModel::findFirst($params))
			)
			{
				throw new Exception(Text::_('403-message'), 403);
			}

			$user = User::getInstance($entity)->setActive();
		}

		if (!$this->isAuthorized($user))
		{
			throw new Exception(Text::_('403-message'), 403);
		}

		Event::trigger('onApiAfterGuard', ['Api']);
	}
}