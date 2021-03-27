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

	/** @var string $uploadPath */
	protected $uploadPath = BASE_PATH . '/public/upload';

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
			$pathName = preg_replace('#^.+/index#', '', $this->uri->getActive()->toString(false));
			$resource = $this->uploadPath . '/' . trim('/' . $pathName . '/', '/.');
			$resource = preg_replace('#/+#', '/', $resource);

			if ($fileBaseName = $this->request->get('file'))
			{
				$resource .= '/' . ltrim($fileBaseName, '/.');
			}

			if (is_file($resource) && FileSystem::remove($resource))
			{
				$thumbsPath = dirname($resource) . '/thumbs';
				$fileName   = FileSystem::stripExt(basename($resource));

				if (is_dir($thumbsPath) && ($thumbs = FileSystem::scanFiles($thumbsPath)))
				{
					foreach ($thumbs as $thumb)
					{
						if (preg_match('#' . preg_quote($fileName, '#') . '_[0-9]+x[0-9]+#', $thumb))
						{
							FileSystem::remove($thumb);
						}
					}
				}

				if ($entity = $this->model->findFirst(['conditions' => 'file = :file:', 'bind' => ['file' => $pathName]]))
				{
					$entity->delete();
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
					'message' => Text::_('the-folder-deleted', ['folder' => $pathName]),
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
			'conditions' => 'type = :type: AND createdBy IN (0, :user:) AND ',
			'bind'       => [
				'type' => 'image',
				'user' => (int) User::getActive()->id,
			],
		];

		if ($baseDir = $this->getCurrentDirs(false))
		{
			$params['conditions']      .= 'file LIKE :like: AND file NOT LIKE :notLike:';
			$params['bind']['like']    = $baseDir . '/%';
			$params['bind']['notLike'] = $baseDir . '/%/%';
		}
		else
		{
			$params['conditions']      .= 'file NOT LIKE :notLike:';
			$params['bind']['notLike'] = '%/%';
		}

		$currentDir = $this->getCurrentDirs();
		$uploadDirs = FileSystem::scanDirs($currentDir, false, function ($dir) {
			if (in_array(basename($dir), ['thumbs', 'entities']))
			{
				return false;
			}
		});

		$this->view->setVars(
			[
				'uri'         => $uri,
				'uploadPath'  => $this->uploadPath,
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
			if (is_dir($this->uploadPath . '/' . $subDirs))
			{
				$currentDirs = $subDirs;
			}
		}

		if ($fullPath)
		{
			return rtrim($this->uploadPath . '/' . $currentDirs, '/');
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

			$file     = new File($currentFile);
			$fileName = $file->getName();
			$mime     = strtolower($file->getRealType());

			if ($error = $file->getError())
			{
				$errorMessage = $error;
			}
			elseif (strpos($mime, 'image/') !== 0)
			{
				$errorMessage = Text::_('file-not-image-message', ['name' => $fileName]);
			}
			elseif ($file->moveTo($this->getCurrentDirs() . '/' . $fileName))
			{
				$data = [
					'file'      => ltrim($this->getCurrentDirs(false) . '/' . $fileName, '/.'),
					'type'      => 'image',
					'mime'      => $mime,
					'createdAt' => (new Date)->toSql(),
					'createdBy' => User::getActive()->id,
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

