<?php

namespace App\Mvc\Controller;

use App\Helper\Event;
use App\Helper\Text;
use App\Helper\Toolbar;
use App\Mvc\Model\Role;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Form\FormsManager;
use MaiVu\Php\Registry;

class AdminRoleController extends AdminControllerBase
{
	/**
	 * @var Role
	 */
	public $model = 'Role';

	/**
	 * @var null
	 */
	public $stateField = null;

	/**
	 * @var string
	 */
	public $role = 'super';

	public function permitAction()
	{
		$packages = [
			'media' => [
				'upload'    => 'core-upload',
				'delete'    => 'core-delete',
				'manageOwn' => 'core-manage-own',
			],
			'tag'   => [
				'create'    => 'core-create',
				'edit'      => 'core-edit',
				'editState' => 'core-edit-state',
				'delete'    => 'core-delete',
				'manageOwn' => 'core-manage-own',
			],
			'user'  => [
				'create'    => 'core-create',
				'edit'      => 'core-edit',
				'delete'    => 'core-delete',
				'activate'  => 'core-activate',
				'manageOwn' => 'core-manage-own',
			],
		];

		foreach (Event::getPlugins() as $group => $plugins)
		{
			foreach ($plugins as $plugin)
			{
				$manifest = $plugin->registry('manifest');

				if ($permissions = $manifest->get('permissions', []))
				{
					$group              = $manifest->get('group');
					$name               = $manifest->get('name');
					$package            = strtolower($group . '-' . $name);
					$packages[$package] = $permissions;
				}
			}
		}

		ksort($packages);
		$roles   = Role::find(
			[
				'type = \'M\' OR (type = \'R\' AND protected = \'N\')',
				'order' => 'id ASC',
			]
		);
		$pkgName = $this->request->get('package', null, array_keys($packages)[0]);

		if (!isset($packages[$pkgName]))
		{
			$pkgName = 'media';
		}

		$packages[$pkgName] = array_merge(
			[
				'admin'  => 'core-admin',
				'manage' => 'core-manage',
			],
			$packages[$pkgName]
		);

		$url          = $this->uri::route('role/permit', ['package' => $pkgName]);
		$formsManager = new FormsManager;
		$rolesMaps    = [];

		foreach ($roles as $role)
		{
			$formFieldsData = [];
			$bindData       = new Registry($role->permissions ?: '{}');
			$formName       = $pkgName . '.' . $role->id;

			foreach ($packages[$pkgName] as $permission => $label)
			{
				if (preg_match('/^[a-zA-Z0-9]+$/', $permission))
				{
					$formFieldData = [
						'name'    => $permission,
						'type'    => 'Switcher',
						'checked' => $bindData->get($pkgName . '.' . $permission) === 'Y',
						'label'   => $label,
						'value'   => 'Y',
						'filters' => ['yesNo'],
					];

					if ($permission !== 'admin')
					{
						if ($permission === 'manage')
						{
							$formFieldData['showOn'] = $formName . '.admin:!Y';
						}
						else
						{
							$formFieldData['showOn'] = $formName . '.admin:!Y & ' . $formName . '.manage:Y';
						}
					}

					$formFieldsData[] = $formFieldData;
				}
			}

			$formsManager->set($role->name, Form::create($formName, $formFieldsData));
			$rolesMaps[$role->id] = $role;
		}

		if ($this->request->isMethod('POST'))
		{
			if ($formsManager->isValidRequest())
			{
				foreach ($formsManager->getData()->get($pkgName, []) as $roleId => $permissions)
				{
					if (isset($rolesMaps[$roleId]))
					{
						$data = new Registry($rolesMaps[$roleId]->permissions ?: '{}');

						foreach ($permissions as $action => $value)
						{
							$data->set($pkgName . '.' . $action, $value);
						}

						$rolesMaps[$roleId]->assign(['permissions' => $data->toString()])->save();
					}
				}

				$this->flashSession->success(Text::_('permissions-saved-msg'));
			}
			else
			{
				$this->flashSession->success(Text::_('permissions-failure-msg'));
			}

			$this->uri::redirect($url);
		}

		Toolbar::add('save', $url, 'cloud-check');
		$this->view->setVars(
			[
				'roles'        => $roles,
				'package'      => $pkgName,
				'packages'     => $packages,
				'formsManager' => $formsManager,
			]
		);
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		$type = $formsManager->get('Role')->getField('type');

		if ($this->model->isProtected())
		{
			$type->set('disabled', true);
		}

		$type->set('checked', in_array($this->model->type, ['S', 'M']));
	}

	protected function doBeforeSave(&$validData, $isNew)
	{
		if ($this->model->isProtected())
		{
			$validData['type'] = $this->model->type;
		}
		else
		{
			$validData['type'] = $validData['type'] === 'Y' ? 'M' : 'R';
		}
	}
}
