<?php

namespace App\Mvc\Controller;

use App\Helper\Assets;
use App\Helper\Database;
use App\Helper\FileSystem;
use App\Helper\MetaData;
use App\Helper\Service;
use App\Helper\Text;
use App\Mvc\Model\Template;
use MaiVu\Php\Form\Field\Switcher;
use MaiVu\Php\Form\FormsManager;
use Throwable;

class AdminTemplateController extends AdminControllerBase
{
	/**
	 * @var Template
	 */
	public $model = 'Template';

	/**
	 * @var null
	 */
	public $stateField = null;

	/**
	 * @var string
	 */
	public $role = 'super';

	public function handleAction()
	{
		$type   = $this->request->getPost('type', 'string', 'folder');
		$remove = $this->request->getPost('task', 'string', 'load') === 'remove';
		$source = trim($this->request->getPost('source', 'string', ''), '/');
		$path   = $this->getTemplatePath('');

		if ($type === 'folder')
		{
			if (is_dir($path . '/' . $source))
			{
				if ($remove)
				{
					if (FileSystem::scanFiles($path . '/' . $source, true))
					{
						return $this->response->setJsonContent(
							[
								'success' => false,
								'message' => Text::_('remove-not-empty-folder-msg'),
							]
						);
					}

					FileSystem::remove($path . '/' . $source);
				}
				else
				{
					$this->persistentData($source);
				}
			}

			return $this->responseData();
		}

		if (is_file($path . '/' . $source))
		{
			if ($remove)
			{
				if (FileSystem::remove($path . '/' . $source))
				{
					return $this->responseData();
				}

				return $this->response->setJsonContent(
					[
						'success' => false,
						'message' => Text::_('cannot-delete-the-resource'),
					]
				);
			}

			MetaData::getInstance()->ignoreRender();
			return $this->response->setJsonContent(
				[
					'success' => true,
					'data'    => file_get_contents($path . '/' . $source),
				]
			);
		}

		return $this->response->setJsonContent(
			[
				'success' => false,
				'message' => 'Resource not found.',
			]
		);
	}

	protected function getTemplatePath($source = null)
	{
		$tplPath = APP_PATH . '/Tmpl/Site/Template-' . $this->model->id;

		if (!is_dir($tplPath))
		{
			try
			{
				FileSystem::copy(APP_PATH . '/Tmpl/System/Template/Site/Default', $tplPath, true);
			}
			catch (Throwable $e)
			{
				$this->flashSession->warning($e->getMessage());
			}
		}

		if (null === $source)
		{
			$source = $this->persistentData();
		}

		if ($source)
		{
			$tplPath .= '/' . $source;
		}

		return $tplPath;
	}

	protected function persistentData($value = null)
	{
		$key = 'template.source.' . $this->model->id;

		if (null !== $value)
		{
			$this->persistent->set($key, $value);
		}

		return $this->persistent->get($key, '');
	}

	protected function responseData()
	{
		return $this->response->setJsonContent(
			[
				'success' => true,
				'data'    => $this->model->getFormsManager()
					->get('Template')
					->getField('resources')
					->set('tplPath', $this->getTemplatePath())
					->set('subDirs', $this->persistentData())
					->toString(),
			]
		);
	}

	public function newResourceAction()
	{
		$source = $this->persistentData();
		$path   = $this->getTemplatePath($source);
		$name   = preg_replace('/[^a-z0-9_\-\.]/i', '', $this->request->getPost('name', 'string', ''));
		$name   = trim($name, '/.');

		if ($name)
		{
			$regex = '/\.([a-z0-9]+)$/i';
			$ext   = '';

			if (preg_match($regex, $name, $matches))
			{
				$ext  = $matches[1];
				$name = preg_replace($regex, '', $name);
			}

			if ($ext)
			{
				file_put_contents($path . '/' . $name . '.' . $ext, '');
			}
			elseif (!is_dir($path . '/' . $name))
			{
				@mkdir($path . '/' . $name, 0755);
			}
		}

		return $this->responseData();
	}

	public function renameAction()
	{
		$path   = $this->getTemplatePath('');
		$source = trim($this->request->getPost('source', 'string', ''), '/\\\\.');
		$name   = preg_replace('/[^a-z0-9_\-\.]/i', '', $this->request->getPost('name', 'string', ''));
		$name   = trim($name, '/.');

		if ($name
			&& $source
			&& file_exists($path . '/' . $source)
		)
		{
			$oldName = $path . '/' . $source;
			$newName = dirname($oldName) . '/' . $name;
			rename($oldName, $newName);
		}

		return $this->responseData();
	}

	public function fileAction()
	{
		$path     = $this->getTemplatePath();
		$file     = basename(trim($this->request->getPost('file', null, ''), '/\\\\.'));
		$contents = $this->request->getPost('contents');

		if (empty($contents))
		{
			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => 'File contents can not be empty.',
				]
			);
		}

		if (empty($file) || !is_file($path . '/' . $file))
		{
			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => 'File not found.',
				]
			);
		}

		if (!file_put_contents($path . '/' . $file, $contents))
		{
			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => Text::_('file-save-failure-msg'),
				]
			);
		}

		return $this->response->setJsonContent(
			[
				'success' => true,
				'message' => Text::_('file-saved-msg'),
			]
		);
	}

	public function indexAction()
	{
		parent::indexAction();
		$this->view->setVar(
			'switcher',
			Switcher::create(
				[
					'name'  => 'isDefault',
					'type'  => 'Switcher',
					'value' => 'Y',
					'class' => 'tpl-toggle-default',
				]
			)
		);
		$action = $this->uri->routeTo('toggle');
		Text::script('name');
		Text::script('please-wait-msg');
		Text::script('remove-folder-confirm-msg');
		Text::script('remove-file-confirm-msg');
		Assets::inlineJs(<<<JS
_$.ready(function ($) {
   	 $('.tpl-toggle-default').on('change', function () {
   	     $('#admin-list-form')
   	     	.attr('action', '{$action}/' + $(this).parent('[data-template-id]').data('templateId'))
   	     	.submit();
   	 });
});
JS
		);
	}

	public function toggleAction(Template $template)
	{
		if ($template->assign(['isDefault' => 'Y'])->save())
		{
			$this->flashSession->success(Text::_('default-template-success', ['name' => $template->name]));
			Service::db()
				->update(
					Database::table('templates'),
					['isDefault'],
					['N'],
					'id <> ' . (int) $template->id
				);
		}

		return $this->uri::redirect($this->uri->routeTo('index'));
	}

	public function resetToDefaultOriginTemplateAction($id)
	{
		$path = APP_PATH . '/Tmpl/Site/Template-' . $id;

		if (is_dir($path))
		{
			FileSystem::remove($path);
		}

		return $this->uri::redirect($this->uri->routeTo('index'));
	}

	protected function indexToolBar($activeState = null, $excludes = ['add'])
	{
		parent::indexToolBar($activeState, $excludes);
	}

	protected function prepareFormsManager(FormsManager $formsManager)
	{
		Assets::add('js/template.js');
		$source = $this->persistentData();
		$formsManager->get('Template')
			->getField('resources')
			->set('tplPath', $this->getTemplatePath($source))
			->set('subDirs', $source);
	}
}
