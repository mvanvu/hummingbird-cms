<?php

namespace MaiVu\Hummingbird\Lib;

use Phalcon\Loader;
use Phalcon\Http\Response;
use Phalcon\Events\Event;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use MaiVu\Hummingbird\Lib\Helper\Asset;
use MaiVu\Hummingbird\Lib\Helper\Config;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\State;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Helper\Event as EventHelper;
use MaiVu\Hummingbird\Lib\Mvc\View\ViewBase;
use MaiVu\Php\Registry;
use MatthiasMullie\Minify;
use Exception;

class CmsApplication extends Application
{
	public function execute()
	{
		try
		{
			$eventsManager = $this->di->getShared('eventsManager');
			$eventsManager->attach('application:beforeSendResponse', $this);
			$plugins      = EventHelper::getPlugins();
			$systemEvents = [
				'application:beforeSendResponse',
				'dispatch:beforeExecuteRoute',
				'dispatch:beforeException',
				'dispatch:beforeDispatch',
				'dispatch:afterDispatch',
				'dispatch:afterInitialize',
			];

			foreach ($plugins['System'] as $className => $config)
			{
				$handler = EventHelper::getHandler($className, $config);

				foreach ($systemEvents as $systemEvent)
				{
					$eventsManager->attach($systemEvent, $handler);
				}
			}

			// Update view dirs
			define('TPL_SITE_PATH', APP_PATH . '/Tmpl/Site/' . Config::get('siteTemplate', 'Hummingbird'));
			define('TPL_ADMINISTRATOR_PATH', APP_PATH . '/Tmpl/Administrator');
			define('TPL_SYSTEM_PATH', APP_PATH . '/Tmpl/System');

			if (Uri::isClient('site'))
			{
				$viewDirs = [
					TPL_SITE_PATH . '/Tmpl/',
					TPL_SITE_PATH . '/',
				];
			}
			else
			{
				$viewDirs = [
					TPL_ADMINISTRATOR_PATH . '/',
				];
			}

			foreach (['System', 'Cms'] as $plgGroup)
			{
				if (isset($plugins[$plgGroup]))
				{
					/**
					 * @var string   $pluginClass
					 * @var Registry $pluginConfig
					 */


					foreach ($plugins[$plgGroup] as $pluginClass => $pluginConfig)
					{
						$pluginName = $pluginConfig->get('manifest.name');
						$pluginPath = PLUGIN_PATH . '/' . $plgGroup . '/' . $pluginName;
						$psrPaths   = [];

						if (is_dir($pluginPath . '/Tmpl'))
						{
							$viewDirs[] = $pluginPath . '/Tmpl/';
						}

						if (is_dir($pluginPath . '/Library'))
						{
							$psrPaths['MaiVu\\Hummingbird\\Lib'] = $pluginPath . '/Library';
						}

						if (is_dir($pluginPath . '/Widget'))
						{
							$psrPaths['MaiVu\\Hummingbird\\Widget'] = $pluginPath . '/Widget';
						}

						if ($psrPaths)
						{
							(new Loader)
								->registerNamespaces($psrPaths, true)
								->register();
						}
					}
				}
			}

			$viewDirs[] = TPL_SYSTEM_PATH . '/';

			/** @var ViewBase $view */
			$view       = $this->di->getShared('view');
			$requestUri = $_SERVER['REQUEST_URI'];

			if (Config::get('siteOffline') === 'Y'
				&& !User::getInstance()->access('super')
			)
			{
				$this->view->setMainView('Offline/Index');

				if (strpos($requestUri, '/user/') !== 0)
				{
					$requestUri = '';
				}
			}
			else
			{
				$view->setMainView('Index');
			}

			$view->setViewsDir($viewDirs);
			$this->setEventsManager($eventsManager);
			$this->handle($requestUri)->send();
		}
		catch (Exception $e)
		{
			if (DEVELOPMENT_MODE)
			{
				// To Phalcon Debug catch this
				throw $e;
			}

			try
			{
				if (User::getInstance()->access('super'))
				{
					State::setMark('exception', $e);
				}

				/**
				 * @var Dispatcher $dispatcher
				 * @var View       $view
				 */
				$dispatcher = $this->getDI()->getShared('dispatcher');
				$dispatcher->setControllerName(Uri::isClient('administrator') ? 'admin_error' : 'error');
				$dispatcher->setActionName('show');
				$dispatcher->setParams(
					[
						'code'    => $e->getCode(),
						'message' => $e->getMessage(),
					]
				);

				$view = $this->getDI()->getShared('view');
				$view->start();
				$dispatcher->dispatch();
				$view->render(
					$dispatcher->getControllerName(),
					$dispatcher->getActionName(),
					$dispatcher->getParams()
				);
				$view->finish();
				echo $view->getContent();
			}
			catch (Exception $e2)
			{
				debugVar($e2->getMessage());
			}
		}
	}

	protected function getCompressor($type)
	{
		if ('css' === $type)
		{
			$compressor = new Minify\CSS;
			$compressor->setImportExtensions(
				[
					'gif' => 'data:image/gif',
					'png' => 'data:image/png',
					'svg' => 'data:image/svg+xml',
				]
			);
		}
		else
		{
			$compressor = new Minify\JS;
		}

		return $compressor;
	}

	protected function compressAssets()
	{
		$basePath = PUBLIC_PATH . '/assets';
		$assets   = Factory::getService('assets');

		foreach (Asset::getFiles() as $type => $files)
		{
			$fileName = md5(implode(':', $files)) . '.' . $type;
			$filePath = $basePath . '/compressed/' . $fileName;
			$fileUri  = ROOT_URI . '/assets/compressed/' . $fileName . (DEVELOPMENT_MODE ? '?' . time() : '');
			$hasAsset = is_file($filePath);
			$ucType   = ucfirst($type);
			$addFunc  = 'add' . $ucType;

			if ($hasAsset && !DEVELOPMENT_MODE)
			{
				call_user_func_array([$assets, $addFunc], [$fileUri, false]);
				continue;
			}

			$compressor = self::getCompressor($type);

			foreach ($files as $file)
			{
				$compressor->add($file);
			}

			if (!is_dir($basePath . '/compressed/'))
			{
				mkdir($basePath . '/compressed/', 0777, true);
			}

			if ($compressor->minify($filePath))
			{
				chmod($filePath, 0777);
				call_user_func_array([$assets, $addFunc], [$fileUri, false]);
			}

			unset($compressor);
		}
	}

	public function beforeSendResponse(Event $event, CmsApplication $app, Response $response)
	{
		$request = $this->di->getShared('request');

		if ($request->isAjax())
		{
			return;
		}

		/** @var Asset $assets */
		$this->compressAssets();
		$assets = $this->di->getShared('assets');

		// Compress CSS
		ob_start();
		$assets->outputCss();
		$assets->outputInlineCss();
		$content = str_replace('</head>', ob_get_clean() . '</head>', $response->getContent());

		// Compress JS
		ob_start();
		$assets->outputJs();
		$assets->outputInlineJs();
		$code = Asset::getCode() . ob_get_clean();

		// Extra code (in the footer)
		$content = str_replace('</body>', $code . '</body>', $content);
		$response->setContent($content);
	}
}