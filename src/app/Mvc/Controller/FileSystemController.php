<?php

namespace App\Mvc\Controller;

use App\Helper\Config;
use App\Helper\FileSystem;
use App\Helper\Service;
use App\Helper\Text;
use App\Helper\User;

class FileSystemController extends ControllerBase
{
	public function uploadAction()
	{
		$crypt     = Service::crypt();
		$encrypted = $this->request->getPost('encrypted', 'string');
		$tmpUpload = $this->request->getPost('tmpUpload') ?? '1';
		$secret    = Config::get('secret.cryptKey');

		if (empty($encrypted)
			|| !($encrypted = base64_decode($encrypted))
			|| !($decrypted = $crypt->decrypt($encrypted, $secret))
			|| !($configData = json_decode($decrypted, true))
			|| !array_key_exists('role', $configData)
			|| !array_key_exists('permission', $configData)
		)
		{
			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => 'Invalid request',
				]
			);
		}

		$user       = User::getActive();
		$role       = $configData['role'] ?? 'register';
		$permission = $configData['permission'] ?? null;

		if (!$user->is($role) || (null !== $permission && !$user->authorise($permission)))
		{
			return $this->response->setJsonContent(
				[
					'success' => false,
					'message' => 'Access denied.',
				]
			);
		}

		$isPrivate     = (bool) $configData['isPrivate'] ?? true;
		$files         = $this->request->getUploadedFiles(true);
		$fileMaxSize   = FileSystem::sizeToBytes($configData['fileMaxSize'] ?? '2M'); // 2MB
		$uploadMaxSize = FileSystem::sizeToBytes(ini_get('upload_max_filesize'));
		$accepts       = explode(',', $configData['accept'] ?? 'image/*');
		$uploadPath    = $isPrivate ? BASE_PATH . '/storages/upload' : PUBLIC_PATH . '/upload';

		if ($fileMaxSize > $uploadMaxSize)
		{
			$fileMaxSize = $uploadMaxSize;
		}

		$userId     = $configData['targetUserId'] ?? $user->id;
		$basePath   = $userId ? 'u/' . $userId : 'tmp/' . uniqid(time());
		$uploadPath .= '/' . $basePath;
		$uploaded   = [];

		foreach ($files as $file)
		{
			if ($fileMaxSize < $file->getSize())
			{
				$size = $fileMaxSize / (1024 * 1024);

				if ($size < 1)
				{
					$size = round(($fileMaxSize / 1024), 2) . 'KB';
				}
				else
				{
					$size = round($size, 2) . 'MB';
				}

				return $this->response->setJsonContent(
					[
						'success' => false,
						'message' => Text::_('invalid-upload-file-size-msg', ['size' => $size]),
					]
				);
			}

			$mime    = $file->getRealType();
			$isValid = false;
			list($l1, $r1) = explode('/', $mime, 2);

			foreach ($accepts as $accept)
			{
				list($l2, $r2) = explode('/', $accept, 2);

				if ($mime === $accept || ($l1 === $l2 && $r2 === '*'))
				{
					$isValid = true;
					break;
				}
			}

			if (!$isValid)
			{
				return $this->response->setJsonContent(
					[
						'success' => false,
						'message' => 'Invalid file type',
					]
				);
			}

			$fileName = FileSystem::makeSafe($file->getName());
			$realName = md5(uniqid($fileName) . time()) . '_' . $fileName;

			if (!$tmpUpload)
			{
				if (!is_dir($uploadPath))
				{
					mkdir($uploadPath, 0755, true);
				}

				if (!$file->moveTo($uploadPath . '/' . $realName))
				{
					return $this->response->setJsonContent(
						[
							'success' => false,
							'message' => Text::_('cannot-upload-the-file', ['file' => $fileName]),
						]
					);
				}
			}
			
			if ($isPrivate)
			{
				$url = $tmpUpload
					? 'data:' . $mime . ';base64, ' . base64_encode(file_get_contents($file->getTempName()))
					: ROOT_URI . '/storages/file/' . base64_encode(
						$crypt->encrypt(
							json_encode(
								[
									'file'       => $uploadPath . '/' . $realName,
									'role'       => $role,
									'isPrivate'  => $isPrivate,
									'permission' => $permission,
								]
							),
							$secret
						)
					);
			}
			else
			{
				$url = $tmpUpload
					? 'data:' . $mime . ';base64, ' . base64_encode(file_get_contents($file->getTempName()))
					: ROOT_URI . '/upload/' . $basePath . '/' . $realName;
			}

			$data = [
				'name'      => $fileName,
				'url'       => $url,
				'base'      => $tmpUpload ? $url : $basePath . '/' . $realName,
				'isPrivate' => $isPrivate,
				'isImage'   => FileSystem::isImage($fileName),
			];

			$uploaded[] = [
				'data' => $data,
				'html' => $this->view->getPartial('Form/Field/UploadedFile', ['file' => $data])
			];
		}

		return $this->response->setJsonContent(
			[
				'success' => true,
				'data'    => $uploaded,
			]
		);
	}

	public function handleAction($base64)
	{
		if ($key = base64_decode($base64))
		{
			$json = json_decode(Service::crypt()->decrypt($key, Config::get('secret.cryptKey')), true) ?: [];

			if (!empty($json['file'])
				&& !empty($json['role'])
				&& array_key_exists('isPrivate', $json)
				&& array_key_exists('permission', $json)
				&& is_file($json['file']))
			{
				$prefix = $json['isPrivate'] ? BASE_PATH . '/storages/upload/' : PUBLIC_PATH . '/upload/';

				if (strpos($json['file'], $prefix) === 0)
				{
					$user = User::getActive();

					if (!$user->is($json['role']) || (null !== $json['permission'] && !$user->authorise($json['permission'])))
					{
						return $this->response->setJsonContent(
							[
								'success' => false,
								'message' => 'Access denied.',
							]
						);
					}

					$this->response->setStatusCode(200);

					if ($this->request->isMethod('DELETE'))
					{
						FileSystem::remove($json['file']);

						return $this->response->setJsonContent(['success' => true]);
					}

					$name = basename($json['file']);

					if (FileSystem::isImage($name))
					{
						return $this->response
							->setContentType('image/' . strtolower(FileSystem::getExt($name)))
							->setContent(file_get_contents($json['file']));
					}

					FileSystem::stream($json['file']);
				}
			}
		}

		User::forward403();
	}
}