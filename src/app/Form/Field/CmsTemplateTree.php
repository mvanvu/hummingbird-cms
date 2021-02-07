<?php

namespace App\Form\Field;

use App\Helper\FileSystem;
use App\Helper\Service;
use MaiVu\Php\Form\Field;

class CmsTemplateTree extends Field
{
	protected $tplPath = APP_PATH . '/Tmpl/Site/Sparrow';

	protected $subDirs = '';

	public function toString()
	{
		$folders = FileSystem::scanDirs($this->tplPath);
		$files   = FileSystem::scanFiles($this->tplPath);

		return Service::view()->getPartial(
			'Form/Field/TemplateTree',
			[
				'field'   => $this,
				'folders' => $folders,
				'files'   => $files,
				'subDirs' => $this->subDirs,
			]
		);
	}
}
