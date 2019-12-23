<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use MaiVu\Hummingbird\Lib\Helper\FileSystem;
use MaiVu\Hummingbird\Lib\Mvc\Model\Config;
use MaiVu\Hummingbird\Lib\Helper\Event;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Helper\Config as CmsConfig;
use MaiVu\Hummingbird\Lib\Helper\Form as FormHelper;
use MaiVu\Hummingbird\Lib\Helper\Toolbar;
use MaiVu\Hummingbird\Lib\Plugin;
use MaiVu\Hummingbird\Lib\Form\Form;
use MaiVu\Php\Registry;
use function MaiVu\Hummingbird\Lib\debugVar;

class AdminPluginController extends AdminControllerBase
{
	/** @var Config */
	public $model = 'Config';

	/** @var string */
	public $pickedView = 'Plugin';

	protected function savePlugin($context, array $data = [], $merge = true)
	{
		$entity = CmsConfig::getEntity($context);

		if ($merge)
		{
			$curData = $entity->data ? (json_decode($entity->data, true) ?: []) : [];
			$data    = array_merge($curData, $data);
		}

		if ($entity->assign(['data' => $data])->save())
		{
			return $entity;
		}

		return false;
	}

	public function indexAction()
	{
		$this->tag->setTitle(Text::_('sys-plugins'));
		$plugins = Event::getPlugins(true);
		$group   = $this->request->getPost('group', ['trim', 'string'], '');
		$plugin  = $this->request->getPost('plugin', ['trim', 'string'], '');

		if ($this->request->isPost()
			&& FormHelper::checkToken()
			&& isset($plugins[$group][$plugin])
			&& User::getInstance()->access('super')
		)
		{
			/**
			 * @var Plugin   $handler
			 * @var Registry $config
			 */

			$config = $plugins[$group][$plugin];
			$name   = $config->get('manifest.name');
			$config->set('active', !$config->get('active'));
			$context = 'cms.config.plugin.' . strtolower($group . '.' . $name);

			if (false !== $this->savePlugin($context, $config->toArray(), false))
			{
				/** @var Plugin $handler */
				$handler = Event::getHandler($plugin, $config);

				if ($config->get('active'))
				{
					$handler && $handler->activate();
					$message = Text::_('activate-plugin-successfully', ['group' => $group, 'name' => $name]);
					$this->flashSession->success($message);
				}
				else
				{
					$handler && $handler->deactivate();
					$message = Text::_('deactivate-plugin-successfully', ['group' => $group, 'name' => $name]);
					$this->flashSession->warning($message);
				}

				return $this->response->redirect(Uri::getInstance(['uri' => 'plugin/index'])->toString(), true);
			}
		}

		Toolbar::add('refresh', $this->uri->routeTo('refresh'), 'plug');
		$this->view->setVars(
			[
				'plugins' => $plugins,
			]
		);
	}

	public function editAction()
	{
		$group  = $this->dispatcher->getParam('group');
		$plugin = $this->dispatcher->getParam('plugin');

		Event::loadPluginLanguage($group, $plugin);
		$plugins     = Event::getPlugins(true);
		$pluginClass = 'MaiVu\\Hummingbird\\Plugin\\' . $group . '\\' . $plugin . '\\' . $plugin;

		if (!isset($plugins[$group][$pluginClass]))
		{
			$this->dispatcher->forward(
				[
					'controller' => 'admin_error',
					'action'     => 'show',
				]
			);

			return false;
		}

		/** @var Registry $config */
		// Reload config file to correct the content language
		$manifest     = new Registry(PLUGIN_PATH . '/' . $group . '/' . $plugin . '/Config.php');
		$paramsForm   = new Form('FormData');
		$hasParams    = $manifest->has('params');
		$context      = strtolower('cms.config.plugin.' . $group . '.' . $plugin);
		$configEntity = CmsConfig::getEntity($context);
		$configData   = $configEntity->data ? (json_decode($configEntity->data, true) ?: []) : [];
		$paramsData   = isset($configData['params']) ? $configData['params'] : [];

		if ($hasParams)
		{
			$paramsForm->load($manifest->get('params'));

			if (!empty($configData['params']))
			{
				$paramsForm->bind($configData['params']);
			}
		}

		if ($hasParams
			&& $this->request->isPost()
			&& $this->request->getPost('action') === 'save'
		)
		{
			$postData   = $this->request->getPost('FormData', null, []);
			$paramsData = $paramsForm->bind($postData);

			if ($paramsForm->isValid()
				&& $this->savePlugin($context, ['params' => $paramsData])
			)
			{
				$this->flashSession->success(Text::_('plugin-saved-success', ['plugin' => $plugin]));

				if ($this->request->get('close'))
				{
					return $this->response->redirect($this->uri->routeTo('index'), true);
				}

				return $this->response->redirect($this->uri->routeTo($group . '/' . $plugin), true);
			}
			else
			{
				$this->flashSession->error(Text::_('plugin-save-failed', ['plugin' => $plugin]));

				foreach ($paramsForm->getMessages() as $message)
				{
					$this->flashSession->warning((string) $message);
				}
			}
		}

		$corePlugins = CmsConfig::get('core.plugins');
		$config      = new Registry(
			[
				'manifest'  => $manifest->toArray(),
				'params'    => $paramsData,
				'active'    => !empty($configData),
				'isCmsCore' => in_array($plugin, $corePlugins),
			]
		);

		$this->view->setVars(
			[
				'paramsForm'   => $paramsForm,
				'pluginConfig' => $config,
				'handler'      => $pluginClass,
			]
		);

		// Toolbar
		if (User::getInstance()->access('super') && $hasParams)
		{
			Toolbar::add('save', $this->uri->routeTo($group . '/' . $plugin), 'cloud-check');
			Toolbar::add('save2close', $this->uri->routeTo($group . '/' . $plugin . '/?close=1'), 'save');
		}

		Toolbar::add('close', $this->uri->routeTo('close/0'), 'close');
	}

	public function refreshAction()
	{
		if (User::getInstance()->access('super')
			&& ($groups = FileSystem::scanDirs(PLUGIN_PATH))
		)
		{
			$pluginsData = [];
			$entities    = Config::find(
				[
					'conditions' => 'context LIKE :context:',
					'bind'       => [
						'context' => 'cms.config.plugin.%',
					],
				]
			);

			foreach ($entities as $entity)
			{
				$context = $entity->context;
				$data    = $entity->data;
				$entity->delete();
				$pluginsData[$context] = empty($data) ? [] : (json_decode($data, true) ?: []);
			}

			$corePlugins = CmsConfig::get('core.plugins', []);

			foreach ($groups as $group)
			{
				if ($plugins = FileSystem::scanDirs($group))
				{
					$group = basename($group);

					foreach ($plugins as $plugin)
					{
						$configFile  = $plugin . '/Config.php';
						$plugin      = basename($plugin);
						$pluginClass = 'MaiVu\\Hummingbird\\Plugin\\' . $group . '\\' . $plugin . '\\' . $plugin;
						Event::loadPluginLanguage($group, $plugin);

						if (is_file($configFile) && ($manifest = include $configFile))
						{
							$isCmsCore = in_array($pluginClass, $corePlugins);
							$context   = strtolower('cms.config.plugin.' . $group . '.' . $plugin);

							if (isset($pluginsData[$context]))
							{
								$configData = $pluginsData[$context];
							}
							else
							{
								$configData = [];
							}

							$configData['manifest']  = $manifest;
							$configData['active']    = $isCmsCore || !empty($configData['active']);
							$configData['isCmsCore'] = $isCmsCore;

							if (!isset($configData['params']))
							{
								$configData['params'] = [];
							}

							$title = isset($manifest['title']) ? Text::_($manifest['title']) : $pluginClass;

							if (!$this->savePlugin($context, $configData, false))
							{
								$this->flashSession->error(
									Text::_('refresh-plugin-data-fail',
										[
											'pluginTitle' => $title,
										]
									)
								);
							}
						}
					}
				}
			}
		}

		$this->flashSession->success(Text::_('plugins-refreshed'));

		return $this->response->redirect($this->uri->routeTo('index'), true);
	}
}
