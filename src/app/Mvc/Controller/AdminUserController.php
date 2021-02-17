<?php

namespace App\Mvc\Controller;

use App\Helper\Cookie;
use App\Helper\FileSystem;
use App\Helper\Language;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\User as Auth;
use App\Mvc\Model\Role;
use App\Mvc\Model\User;
use MaiVu\Php\Form\FormsManager;
use Phalcon\Mvc\Model\Query\BuilderInterface;

class AdminUserController extends AdminControllerBase
{
	/**
	 * @var User
	 */
	public $model = 'User';

	/**
	 * @var string
	 */
	public $stateField = null;

	public function loginAction()
	{
		if (!$this->user()->is('guest'))
		{
			return $this->uri::redirect(Uri::home());
		}

		$this->view->setMainView('Login');

		if ($this->request->isPost())
		{
			$username = $this->request->getPost('username');
			$password = $this->request->getPost('password');
			$language = $this->request->getPost('language');

			if (!Auth::login($username, $password))
			{
				$this->flashSession->error(Text::_('login-fail-message'));

				return $this->uri::redirect(Uri::home());
			}

			$forward = $this->request->get('forward', null, Uri::home());
			$uri     = Uri::fromUrl($forward);

			if (!$uri->isInternal())
			{
				$uri = Uri::home();
			}

			if ($language && Language::has($language))
			{
				$uri->setVar('language', Language::get($language)->get('locale.sef'));
			}

			return $uri->redirect($uri->toString(false));
		}
	}

	public function logoutAction()
	{
		$user = $this->user();

		if ($user->is('guest') || !$this->request->isPost())
		{
			$this->flashSession->error(Text::_('access-denied'));

			return false;
		}

		if (Cookie::has('cms.administrator.language'))
		{
			Cookie::remove('cms.administrator.language');
		}

		if (Cookie::has('cms.user.remember'))
		{
			Cookie::remove('cms.user.remember');
		}

		$user->logout();

		return $this->uri::redirect(Uri::getInstance(['uri' => 'user/login']));
	}

	public function indexToolBar($activeState = null, $excludes = ['copy'])
	{
		parent::indexToolBar($activeState, $excludes);
	}

	public function doBeforeSave(&$validData, $isNew)
	{
		$oldAvatar = $this->model->registry('params')->get('avatar', '');
		$newAvatar = $validData['params']['avatar'] ?? '';
		preg_match('/^data:image\/(.+);base64,\s(.+)/', $newAvatar, $matches);

		if (empty($newAvatar) && !empty($oldAvatar))
		{
			FileSystem::remove(PUBLIC_PATH . '/' . $oldAvatar);
		}

		if (!empty($matches[1]))
		{
			$dir  = PUBLIC_PATH . '/upload/u/' . $this->model->id;
			$name = md5($matches[0]) . '_avatar.' . $matches[1];
			$file = $dir . '/' . $name;

			if (!is_dir($dir))
			{
				mkdir($dir, 0755, true);
			}

			if (file_put_contents($file, base64_decode($matches[2])))
			{
				$validData['params']['avatar'] = 'upload/u/' . $this->model->id . '/' . $name;
			}
		}
	}

	protected function prepareIndexQuery(BuilderInterface $query)
	{
		$query->leftJoin(Role::class, 'role.id = item.roleId', 'role');
		$user = $this->user();

		if (!$user->isRoot())
		{
			$authId   = (int) $user->id;
			$whereRaw = 'role.type <> \'S\'';

			if ($user->is('manager'))
			{
				$whereRaw .= ' AND role.type <> \'N\'';
			}

			$query->andWhere('(' . $whereRaw . ' OR item.id = ' . $authId . ')');
		}
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$user     = $this->user();
		$userForm = $formsManager->get('User');
		$role     = $userForm->getField('roleId');
		$pass1    = $userForm->getField('password');
		$pass2    = $userForm->getField('confirmPassword');

		if ($id = $userForm->getField('id')->getValue())
		{
			if ($user->id == $id)
			{
				$userForm->getField('active')->set('readonly', true);
				$role->set('class', 'uk-background-muted uk-select not-chosen uk-disabled');
			}

			$pass1->setValue('')->set('required', false);
			$pass2->setValue('')->set('required', false);
		}

		if ($this->request->isPost() && in_array($this->dispatcher->getActionName(), ['save', 'save2close']))
		{
			$postData = $this->request->getPost($this->mainEditFormName, null, []);

			if (empty($postData['password']) && empty($postData['confirmPassword']))
			{
				$userForm->remove('password')->remove('confirmPassword');
			}
		}
	}
}