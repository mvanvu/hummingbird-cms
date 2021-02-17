<?php

namespace App\Form\Field;

use App\Helper\Config;
use App\Helper\FileSystem;
use App\Helper\Service;
use MaiVu\Php\Form\Field\Select;

class CmsUpload extends Select
{
	protected $accept = 'image/*';

	protected $fileMaxSize = '2M';

	protected $class = 'uk-hidden';

	protected $role = 'manager';

	protected $permission = 'media.upload';

	protected $tmpUpload = true;

	protected $isPrivate = true;

	public function getOptions()
	{
		$options = [];

		if ($value = $this->getValue())
		{
			settype($value, 'array');

			foreach ($value as $option)
			{
				$options[] = [
					'value' => $option,
					'text'  => $option,
				];
			}
		}

		return $options;
	}

	public function getValue()
	{
		$value = $this->value;

		if ($this->multiple && !is_array($value))
		{
			$value = $value ? [$value] : [];
		}

		return $value;
	}

	public function toString()
	{
		$files  = [];
		$crypt  = Service::crypt();
		$secret = Config::get('secret.cryptKey');

		if ($value = $this->getValue())
		{
			settype($value, 'array');

			foreach ($value as $fileBase)
			{
				$name = basename($fileBase);

				if ($this->isPrivate)
				{
					$file = BASE_PATH . '/storages/' . $name;
					$url  = ROOT_URI . '/storages/file/'
						. base64_encode(
							$crypt->encrypt(
								json_encode(
									[
										'file'       => $file,
										'role'       => $this->role,
										'permission' => $this->permission,
									]
								),
								$secret
							)
						);
				}
				else
				{
					$url = ROOT_URI . '/' . $fileBase;
				}

				$files[] = [
					'name'    => preg_replace('/^[a-z0-9]+_/i', '', $name),
					'url'     => $url,
					'base'    => $fileBase,
					'isImage' => FileSystem::isImage($name),
				];
			}
		}

		$encrypted = $crypt->encrypt(
			json_encode(
				[
					'accept'      => $this->accept,
					'fileMaxSize' => $this->fileMaxSize,
					'isPrivate'   => $this->isPrivate,
					'permission'  => $this->permission,
					'role'        => $this->role,
				]
			),
			$secret
		);

		return Service::view()->getPartial(
			'Form/Field/Upload',
			[
				'field'     => $this,
				'files'     => $files,
				'input'     => parent::toString(),
				'encrypted' => base64_encode($encrypted),
			]
		);
	}
}