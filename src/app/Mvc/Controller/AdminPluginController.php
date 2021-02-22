<?php

namespace App\Mvc\Controller;

use App\Helper\Assets;
use App\Helper\Config;
use App\Helper\Event;
use App\Helper\FileSystem;
use App\Helper\Language;
use App\Helper\Text;
use App\Helper\Toolbar;
use App\Helper\Uri;
use App\Mvc\Model\Plugin;
use App\Mvc\Model\Plugin as PluginModel;
use App\Traits\Permission;
use MaiVu\Php\Form\Field\Switcher;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Registry;
use RuntimeException;
use Throwable;
use ZipArchive;

class AdminPluginController extends AdminControllerBase
{
	/**
	 * @var PluginModel
	 */
	public $model = 'Plugin';

	/**
	 * @var string
	 */
	public $pickedView = 'Plugin';

	/**
	 * @var string
	 */
	public $role = 'super';

	/**
	 * @var null
	 */
	public $stateField = null;

	use Permission;

	public function toggleAction(PluginModel $plugin)
	{
		if ($this->request->isPost()
			&& $this->user()->is('super')
			&& $plugin->assign(['active' => $plugin->yes('active') ? 'N' : 'Y'])->save()
		)
		{
			$handler = Event::getHandler($plugin);

			if ($plugin->yes('active'))
			{
				$handler && $handler->callback('activate');
				$message = Text::_('activate-plugin-successfully', ['group' => $plugin->group, 'name' => $plugin->name]);
				$this->flashSession->success($message);
			}
			else
			{
				$handler && $handler->callback('deactivate');
				$message = Text::_('deactivate-plugin-successfully', ['group' => $plugin->group, 'name' => $plugin->name]);
				$this->flashSession->success($message);
			}
		}

		return $this->uri::redirect(Uri::getInstance(['uri' => 'plugin/index'])->toString());
	}

	public function editAction()
	{
		Event::loadPluginLanguage($this->model->group, $this->model->name);

		/** @var Registry $config */
		// Reload config file to correct the content language
		$manifest   = Registry::create($this->model->manifest);
		$paramsForm = Form::create('Plugin');
		$params     = $manifest->get('params', []);
		$isSaving   = $params && $this->request->isPost() && $this->request->getPost('action') === 'save';

		if ($params)
		{
			$paramsForm->load($manifest->get('params'));

			if (!$isSaving)
			{
				$paramsForm->bind(['Plugin' => $this->model->params]);
			}
		}

		if ($isSaving)
		{
			if ($paramsForm->isValidRequest() && $this->model->assign(['params' => $paramsForm->getData()])->save())
			{
				$this->flashSession->success(Text::_('plugin-saved-success', ['plugin' => $this->model->name]));

				if ($this->request->get('close'))
				{
					return $this->uri::redirect($this->uri->routeTo('index'));
				}

				return $this->uri::redirect($this->uri->routeTo('edit/' . $this->model->id));
			}
			else
			{
				$this->flashSession->error(Text::_('plugin-save-failed', ['plugin' => $this->model->name]));

				foreach ($paramsForm->getMessages() as $message)
				{
					$this->flashSession->warning((string) $message);
				}
			}
		}

		// Toolbar
		if ($params)
		{
			Toolbar::add('save', $this->uri->routeTo('edit/' . $this->model->id), 'cloud-check');
			Toolbar::add('save2close', $this->uri->routeTo('edit/' . $this->model->id . '/?close=1'), 'save');
		}

		Toolbar::add('refresh-manifest', $this->uri->routeTo('refresh-manifest/' . $this->model->id), 'history');
		Toolbar::add('close', $this->uri->routeTo('close'), 'close');
		$this->view->setVar('paramsForm', $paramsForm);
		$this->tag->setTitle(Text::_('admin-plugin-edit-title', ['group' => $this->model->group, 'name' => $this->model->name]));
	}

	public function refreshManifestAction(PluginModel $plugin)
	{
		$configPath = PLUGIN_PATH . '/' . $plugin->group . '/' . $plugin->name . '/Config.php';

		if (is_file($configPath))
		{
			$plugin->assign(['manifest' => Registry::create($configPath)->toString()])->save();
			$this->flashSession->success(Text::_('manifest-updated-msg'));
		}

		$this->uri::redirect($this->uri->routeTo('edit/' . $this->model->id));
	}

	public function exportAction(PluginModel $plugin)
	{
		FileSystem::streamFolder(PLUGIN_PATH . '/' . $plugin->group . '/' . $plugin->name, $plugin->group . '-' . $plugin->name . '.zip');
	}

	public function getPackagesAction()
	{
		$t               = '?' . time();
		$packagesChannel = Config::get('packagesChannel');

		if (empty($packagesChannel) || !preg_match('/^https?:.+\.json$/', $packagesChannel))
		{
			$packagesChannel = 'https://raw.githubusercontent.com/mvanvu/hummingbird-packages/master/packages.json';
		}

		$packages = [];
		$plugins  = [];

		foreach (json_decode(file_get_contents($packagesChannel . $t), true) as $url => $title)
		{
			$package = json_decode(file_get_contents($url . $t), true);

			if (!empty($package['source']))
			{
				$packages[$title] = $package;
			}
		}

		foreach (Plugin::find() as $plugin)
		{
			$plugins[$plugin->group][$plugin->name] = $plugin;
		}

		return $this->response->setJsonContent(
			$this->view->getPartial(
				'Plugin/Packages',
				[
					'packages' => $packages,
					'plugins'  => $plugins,
					'language' => Language::getActiveCode(),
				]
			)
		);
	}

	public function indexAction()
	{
		parent::indexAction();
		Assets::add('js/plugins.js');
		$this->view->setVar(
			'switcher',
			Switcher::create(
				[
					'class' => 'switcher-plugin',
					'value' => 'Y',
				]
			)
		);
	}

	public function installPackageAction()
	{
		set_time_limit(0);
		ini_set('memory_limit', '-1');
		$success = false;
		$message = Text::_('cannot-install-package-msg');
		$source  = $this->request->getPost('source', null, '');
		$path    = TMP_PATH . '/install' . time();

		if (!is_dir($path))
		{
			mkdir($path, 0755, true);
		}

		if ($source
			&& preg_match('#^https?.+\.zip$#', $source)
			&& file_put_contents($path . '/' . basename($source), file_get_contents($source))
		)
		{
			$file = $path . '/' . basename($source);
		}
		else
		{
			$files = $this->request->getUploadedFiles(true, true);

			if (!empty($files['package'])
				&& $files['package']->getRealType() === 'application/zip'
				&& $files['package']->moveTo($path . '/' . $files['package']->getName())
			)
			{
				$file = $path . '/' . $files['package']->getName();
			}
		}

		if (!empty($file))
		{
			$zip = new ZipArchive;

			if (true === $zip->open($file) && $zip->extractTo($path))
			{
				$zip->close();
				FileSystem::remove($file);

				if ($configFile = FileSystem::findInPath('Config.php', $path, true))
				{
					$manifest = Registry::create($configFile);
					$regex    = '/^[a-z][a-z0-9]+$/i';
					$version  = $manifest->get('version', '');

					if (($name = (string) $manifest->get('name', ''))
						&& ($group = (string) $manifest->get('group', ''))
						&& preg_match('/^[0-9]\.[0-9](\.[0-9]{1,3})?$/', $version)
						&& preg_match($regex, $group)
						&& preg_match($regex, $group)
						&& $manifest->has('author')
						&& $manifest->has('authorEmail')
					)
					{
						$basePath = dirname($configFile);
						$folder   = PLUGIN_PATH . '/' . $group . '/' . $name;

						/** @var PluginModel $plugin */
						$plugin = PluginModel::findFirst(
							[
								'[group] = :group: AND name = :name:',
								'bind' => [
									'group' => $group,
									'name'  => $name,
								],
							]
						);

						if ($plugin && $plugin->registry('manifest')->get('authorEmail') !== $manifest->get('authorEmail'))
						{
							$message = Text::_('plugin-has-exists-msg', ['group' => $group, 'name' => $name]);
						}
						else
						{
							try
							{

								FileSystem::copy($basePath, $folder, true);
								$plugin = $plugin ?: PluginModel::getInstance();
								$plugin->assign(
									[
										'group'    => $group,
										'name'     => $name,
										'version'  => $version,
										'active'   => $plugin->active,
										'manifest' => $manifest->toString(),
										'params'   => '{}',
										'ordering' => PluginModel::maximum(
												[
													'[group] = :group:',
													'column' => 'ordering',
													'bind'   => [
														'group' => $group,
													],
												]
											) + 1,
									]
								);

								if ($handler = Event::getHandler($plugin))
								{
									$handler->callback('preInstall', [$plugin]);
									$isNew = empty($plugin->id);

									if (!$plugin->save())
									{
										throw new RuntimeException(Text::_('cannot-install-package-msg'));
									}

									if ($isNew)
									{
										$handler->callback('install', [$plugin]);
										$message = Text::_('plugin-installed-success-msg', ['group' => $group, 'name' => $name]);
									}
									else
									{
										$handler->callback('update', [$plugin]);
										$message = Text::_('plugin-updated-success-msg', ['group' => $group, 'name' => $name]);
									}

									$handler->callback('postInstall', [$plugin]);
									$success = true;
								}
							}
							catch (Throwable $e)
							{
								$message = $e->getMessage();
							}
						}
					}
				}
				else
				{
					$message = Text::_('config-file-not-found-msg');
				}
			}
		}

		FileSystem::remove($path);

		if ($success)
		{
			$this->flashSession->success($message);
		}

		return $this->response->setJsonContent(
			[
				'success' => $success,
				'message' => $message,
			]
		);
	}

	public function uninstallPackageAction(PluginModel $plugin)
	{
		try
		{
			if ($plugin->yes('protected'))
			{
				throw new RuntimeException('Can\'t uninstall the protected plugin.');
			}

			$path    = PLUGIN_PATH . '/' . $plugin->group . '/' . $plugin->name;
			$handler = Event::getHandler($plugin);

			if ($plugin->delete())
			{
				if ($handler)
				{
					$handler->callback('uninstall');
				}

				if (is_dir($path))
				{
					FileSystem::remove($path);
				}
			}

			$this->flashSession->success(
				Text::_(
					'plugin-uninstalled-msg',
					[
						'group' => $plugin->group,
						'name'  => $plugin->name,
					]
				)
			);

			return $this->response->setJsonContent(
				[
					'success' => true,
				]
			);
		}
		catch (Throwable $e)
		{
			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => $e->getMessage(),
				]
			);
		}
	}

	protected function indexToolBar($activeState = null, $excludes = [])
	{
		Toolbar::add('installation-packages', '#plugin-modal-container', 'cloud-sync');
	}
}
