<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use Phalcon\Image\Adapter\Gd;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use MaiVu\Hummingbird\Lib\Helper\Event;
use MaiVu\Hummingbird\Lib\Helper\Config;

class AssetsController extends Controller
{
	protected $assetsPath = [];

	public function onConstruct()
	{
		// Assets collections
		$plugins = Event::getPlugins();

		if (isset($plugins['Cms']))
		{
			$this->assetsPath['t'] = APP_PATH . '/Tmpl/Site/' . Config::getTemplate()->name . '/Asset';

			foreach (Event::getPlugins() as $group => $plugins)
			{
				foreach ($plugins as $pluginClass => $pluginConfig)
				{
					if ($assetName = $pluginConfig->get('assetName'))
					{
						$this->assetsPath[$assetName] = PLUGIN_PATH . '/' . $group . '/' . $pluginConfig->get('name') . '/Asset';
					}
				}
			}
		}
	}

	public function serveAction()
	{
		$assetName = $this->dispatcher->getParam('assetName');
		$fileName  = $this->dispatcher->getParam('fileName');
		$fileExt   = $this->dispatcher->getParam('fileExt');
		$type      = $this->dispatcher->getParam('type');
		$isImage   = $type === 'img';
		$fileBase  = ($isImage ? 'Image' : ucfirst($type)) . '/' . $fileName . '.' . $fileExt;

		// Check collection existence
		if (!isset($this->assetsPath[$assetName])
			|| !is_file($this->assetsPath[$assetName] . '/' . $fileBase)
		)
		{
			return $this->dispatcher->forward(
				[
					'controller' => 'error',
					'action'     => 'show',
				]
			);
		}

		$response = new Response;

		if ($isImage)
		{
			$image = new Gd($this->assetsPath[$assetName] . '/' . $fileBase);
			$response->setContentType($image->getMime());
			$response->setContent($image->render());
		}
		else
		{
			switch ($type)
			{
				case 'Js':
					$contentType = 'application/javascript';
					break;

				case 'Jsx':
					$contentType = 'text/babel';
					break;

				default:
					$contentType = 'text/css';
					break;
			}

			$response->setContentType($contentType, 'UTF-8');
			$response->setContent(file_get_contents($this->assetsPath[$assetName] . '/' . $fileBase));
		}

		// Return the response
		return $response;
	}
}