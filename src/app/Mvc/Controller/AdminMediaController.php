<?php

namespace App\Mvc\Controller;

use App\Helper\Assets;
use App\Helper\Date;
use App\Helper\FileSystem;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\User;
use App\Mvc\Model\Media;
use Phalcon\Http\Request\File;

class AdminMediaController extends AdminControllerBase
{
	/** @var Media $model */
	public $model = 'Media';

	public function onConstruct()
	{
		Text::script('confirm-remove-folder');
		Text::script('confirm-remove-image');
		parent::onConstruct();
	}

	public function indexAction()
	{
		Assets::add('js/media.js');
		$this->tag->setTitle(Text::_('manage-media'));

		if ($this->request->isPost()
			&& $this->request->isAjax()
			&& $this->request->getPost('method') === 'delete'
			&& $this->model->authorize('delete')
		)
		{
			$pathName = preg_replace('#^.+/index#', '', $this->uri->getActive()->toPath());
			$pathName = 'media/' . trim($pathName, '/.');
			$pathName = preg_replace('#/+#', '/', rtrim($pathName, '/'));

			if ($fileBaseName = $this->request->get('file'))
			{
				$pathName .= '/' . trim($fileBaseName, '/.');
			}

			$resource = PUBLIC_PATH . '/' . $pathName;

			if (is_file($resource) && FileSystem::remove($resource))
			{
				$thumbsPath = dirname($resource) . '/_thumbs';
				$fileName   = FileSystem::stripExt(basename($resource));

				if (is_dir($thumbsPath))
				{
					FileSystem::scanFiles($thumbsPath, false, function ($thumb) use ($fileName) {
						if (preg_match('#' . preg_quote($fileName, '#') . '_[0-9]+x[0-9]+#', $thumb))
						{
							FileSystem::remove($thumb);
						}
					});
				}

				if ($entity = $this->model->findFirst(['conditions' => 'file = :file:', 'bind' => ['file' => $pathName]]))
				{
					$entity->delete();
				}

				if (strpos($fileBaseName, '_'))
				{
					$fileBaseName = explode('_', $fileBaseName, 2)[1];
				}

				$jsonContent = [
					'success' => true,
					'message' => Text::_('the-file-deleted', ['file' => $fileBaseName]),
				];
			}
			elseif (is_dir($resource) && FileSystem::remove($resource))
			{
				$this->modelsManager->executeQuery('DELETE FROM ' . Media::class . ' WHERE file LIKE :baseDir:',
					[
						'baseDir' => $pathName . '/%',
					]
				);

				$jsonContent = [
					'success' => true,
					'message' => Text::_('the-folder-deleted', ['folder' => basename($resource)]),
				];
			}
			else
			{
				$jsonContent = [
					'success' => false,
					'message' => Text::_('cannot-delete-the-resource'),
				];
			}

			return $this->response->setJsonContent($jsonContent);
		}

		$this->load();
	}

	protected function load()
	{
		$currentDir = $this->getCurrentDirs(false);
		$uri        = Uri::getInstance(['uri' => 'media/index' . ($currentDir ? '/' . $currentDir : '')]);
		$params     = [
			'conditions' => 'type = :type: AND createdBy IN (0, :user:) AND file LIKE :like: AND file NOT LIKE :notLike:',
			'bind'       => [
				'type' => 'image',
				'user' => User::id(),
			],
		];

		if ($baseDir = $this->getCurrentDirs(false))
		{
			$params['bind']['like']    = 'media/' . $baseDir . '/%';
			$params['bind']['notLike'] = 'media/' . $baseDir . '/%/%';
		}
		else
		{
			$params['bind']['like']    = 'media/%';
			$params['bind']['notLike'] = 'media/%/%';
		}

		$currentDir = $this->getCurrentDirs();
		$uploadDirs = FileSystem::scanDirs($currentDir, false, function ($dir) {
			if (in_array(basename($dir), ['_thumbs']))
			{
				return false;
			}
		});
		$this->view->setVars(
			[
				'uri'         => $uri,
				'publicPath'  => PUBLIC_PATH,
				'uploadDirs'  => preg_replace('#^' . preg_quote($currentDir, '#') . '/#', '', $uploadDirs),
				'uploadFiles' => $this->model->find($params),
				'isRaw'       => $this->dispatcher->getParam('format') === 'raw',
			]
		);
	}

	protected function getCurrentDirs($fullPath = true)
	{
		$currentDirs = '';
		$subDirs     = preg_replace('#^.*/' . $this->dispatcher->getActionName() . '#', '', Uri::getActive());
		$subDirs     = trim($subDirs, '/.');
		$this->view->setVar('subDirs', $subDirs);

		if ($subDirs)
		{
			if (is_dir(PUBLIC_PATH . '/media/' . $subDirs))
			{
				$currentDirs = $subDirs;
			}
		}

		if ($fullPath)
		{
			return rtrim(PUBLIC_PATH . '/media/' . $currentDirs, '/');
		}

		return $currentDirs;
	}

	public function uploadAction()
	{
		if ($this->request->isPost() && !empty($_FILES['files']))
		{
			$errorMessage = null;
			$currentFile  = [];

			foreach ($_FILES['files'] as $name => $value)
			{
				$currentFile[$name] = $value[0];
			}

			$file       = new File($currentFile);
			$fileName   = FileSystem::makeSafe($file->getName());
			$mime       = strtolower($file->getRealType());
			$uploadName = md5(User::id() . ':' . $mime . ':' . $fileName . ':' . time()) . '_' . $fileName;
			$currentDir = $this->getCurrentDirs();

			if (!is_dir($currentDir))
			{
				mkdir($currentDir, 0755, true);
			}

			if ($error = $file->getError())
			{
				$errorMessage = $error;
			}
			elseif (strpos($mime, 'image/') !== 0)
			{
				$errorMessage = Text::_('file-not-image-message', ['name' => $fileName]);
			}
			elseif ($file->moveTo($currentDir . '/' . $uploadName))
			{
				$data = [
					'file'      => 'media/' . ltrim($this->getCurrentDirs(false) . '/' . $uploadName, '/.'),
					'type'      => 'image',
					'mime'      => $mime,
					'createdAt' => Date::now('UTC')->toSql(),
					'createdBy' => User::id(),
				];

				if ($entity = $this->model->findFirst(['conditions' => 'file = :file:', 'bind' => ['file' => $data['file']]]))
				{
					$entity->assign($data)->save();
				}
				else
				{
					$this->model->assign($data)->save();
				}
			}
			else
			{
				$errorMessage = Text::_('can-not-upload-image-message', ['name' => $fileName]);
			}

			$this->load();

			return $this->response->setJsonContent([
				'success'    => empty($errorMessage),
				'message'    => $errorMessage ?: Text::_('upload-image-success-message', ['name' => $fileName]),
				'outputHTML' => $this->view->getPartial('Media/Media'),
			]);
		}
	}

	public function createAction()
	{
		$currentDirs = $this->getCurrentDirs();
		$name        = FileSystem::makeSafe($this->request->getPost('name', ['alphanum'], ''));

		if ($this->request->isPost()
			&& $this->request->isAjax()
			&& is_dir($currentDirs)
			&& !empty($name)
			&& !is_dir($currentDirs . '/' . $name)
			&& mkdir($currentDirs . '/' . $name, 0755)
		)
		{
			$this->load();

			return $this->response->setJsonContent(
				[
					'success'    => true,
					'message'    => 'The folder [' . $name . '] is created successfully',
					'outputHTML' => $this->view->getPartial('Media/Media'),
				]
			);
		}

		return $this->response->setJsonContent(
			[
				'success' => false,
				'message' => 'Create new folder failure',
			]
		);
	}
}

