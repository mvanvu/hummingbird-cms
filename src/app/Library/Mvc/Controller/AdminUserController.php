<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Mvc\Dispatcher;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\User as CmsUser;
use MaiVu\Hummingbird\Lib\Mvc\Model\User;
use MaiVu\Hummingbird\Lib\Helper\Date;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Helper\Form as FormHelper;
use MaiVu\Hummingbird\Lib\Form\FormsManager;

class AdminUserController extends AdminControllerBase
{
	/** @var User $model */
	public $model = 'User';

	public $stateField = null;

	public function afterExecuteRoute(Dispatcher $dispatcher)
	{
		$user   = CmsUser::getInstance();
		$access = true;

		if ($this->model->role === 'S'
			&& $user->isRole('manager')
		)
		{
			$access = false;
		}

		if ($this->model->id
			&& $this->model->id != $user->id
			&& $this->model->role === $user->role
		)
		{
			$access = false;
		}

		if (!$access && !$user->access('super'))
		{
			$this->dispatcher->setParams(
				[
					'code'    => '403',
					'title'   => Text::_('403-title'),
					'message' => Text::_('403-message'),
				]
			);

			$this->dispatcher->forward(
				[
					'controller' => 'error',
					'action'     => 'show',
				]
			);
		}
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$generalForm = $formsManager->get('general');
		$role        = $generalForm->getField('role');
		$user        = CmsUser::getInstance();

		if ($id = $generalForm->getField('id')->getValue())
		{
			if ($user->id == $id)
			{
				$active = $generalForm->getField('active');
				$active->set('class', 'uk-background-muted uk-select not-chosen uk-disabled');
				$role->set('class', 'uk-background-muted uk-select not-chosen uk-disabled');
				$role->setValue($user->isRoot() ? 'S' : $user->role);
			}

			$generalForm->getField('password')->setValue('');
		}

		if (!$user->isRoot() && $user->isRole('manager'))
		{
			$role->set(
				'options',
				[
					'R' => Text::_('role-register'),
					'A' => Text::_('role-author'),
					'M' => Text::_('role-manager'),
				]
			);
		}

		if ($this->request->isPost() && $id)
		{
			$password1 = $generalForm->getField('password')->getValue();
			$password2 = $generalForm->getField('confirmPassword')->getValue();

			if (empty($password1) && empty($password2))
			{
				$generalForm
					->remove('password')
					->remove('confirmPassword');
			}
		}
	}

	public function loginAction()
	{

		if (!CmsUser::getInstance()->isGuest())
		{
			return $this->response->redirect(Uri::getInstance(['uri' => '/'])->toString(), true);
		}

		$this->view->setMainView('Login');

		if ($this->request->isPost())
		{
			if (!FormHelper::checkToken())
			{
				return $this->response->redirect(Uri::getInstance(['uri' => '/'])->toString(), true);
			}

			$username = $this->request->getPost('username');
			$password = $this->request->getPost('password');
			$language = $this->request->getPost('language');
			$entity   = User::findFirst(
				[
					'conditions' => 'username = :username: AND active = :active:',
					'bind'       => [
						'username' => $username,
						'active'   => 'Y',
					],
				]
			);

			if (!$entity || !$this->security->checkHash($password, $entity->password))
			{
				$this->flashSession->error(Text::_('login-fail-message'));

				return $this->response->redirect(Uri::getInstance(['uri' => '/'])->toString(), true);
			}

			/** @var  User $entity */
			// Update latest visit date
			$entity->assign(['lastVisitedDate' => Date::getInstance()->toSql()])->save();
			CmsUser::getInstance($entity)->setActive();

			if ($language && Language::has($language))
			{
				$language = Language::get($language);
				$uri      = Uri::getInstance(['language' => $language->get('locale.sef')]);
			}
			else
			{
				$uri = Uri::getActive(true);
			}

			return $this->response->redirect($uri->toString(), true);
		}
	}

	public function logoutAction()
	{
		$user = CmsUser::getInstance();

		if ($user->isGuest()
			|| !$this->request->isPost()
			|| !FormHelper::checkToken()
		)
		{
			$this->flashSession->error(Text::_('access-denied'));

			return false;
		}

		$key = 'cms.administrator.language';

		if ($this->cookies->has($key))
		{
			$this->cookies->delete($key);
		}

		if ($this->cookies->has('cms.user.remember'))
		{
			$this->cookies->delete('cms.user.remember');
		}

		$user->logout();

		return $this->response->redirect(Uri::getInstance(['uri' => 'user/login']), true);
	}

	public function indexToolBar($activeState = null, $excludes = ['copy'])
	{
		parent::indexToolBar($activeState, $excludes);
	}
}
