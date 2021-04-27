<?php

namespace App\Factory;

use App\Helper\Config;
use App\Helper\Constant;
use App\Helper\Event as EventHelper;
use App\Helper\Service;
use App\Helper\State;
use App\Helper\Template;
use App\Helper\Text;
use App\Helper\Uri;
use App\Helper\User;
use Exception;
use Phalcon\Mvc\Application;
use Throwable;

class WebApplication extends Application
{
	public function execute()
	{
		$user = User::getActive();

		try
		{
			// Global event
			EventHelper::trigger('onBootCms', [$this], ['Cms']);
			Template::initialize();

			// Update view dirs
			define('TPL_SYSTEM_PATH', APP_PATH . '/Tmpl/System');
			define('TPL_ADMINISTRATOR_PATH', APP_PATH . '/Tmpl/Administrator');
			define('TPL_SITE_PATH', Template::getTemplatePath());
			$view     = $this->di->getShared('view');
			$viewDirs = [
				Uri::isClient('site') ? TPL_SITE_PATH . '/' : TPL_ADMINISTRATOR_PATH . '/',
				TPL_SYSTEM_PATH . '/',
			];
			$view->setViewsDir(array_merge($viewDirs, $view->getViewsDir()));
			$view->setMainView('Index');
			$requestUri = $_SERVER['REQUEST_URI'];
			$willHandle = true;

			if (Uri::isClient('administrator'))
			{
				if ($user->is('guest'))
				{
					if (strpos($requestUri, '/user/login') === false)
					{
						Uri::redirect(Uri::route('user/login', ['forward' => Uri::getActive()->toString()]));
					}
				}
				elseif (!$user->is(['root', 'super', 'manager']))
				{
					$user->logout();
					throw new Exception(Text::_('403-message'), 403);
				}
			}
			elseif (Config::is('siteOffline') && !$user->is('super'))
			{
				$view->setMainView('Offline/Index');

				if (!$this->request->isPost() || 0 !== strpos($requestUri, '/user/login'))
				{
					$willHandle = false;
					$this->manualResponse('', '');
				}
			}

			if ($willHandle)
			{
				// Fire event before handle request
				EventHelper::trigger('onBeforeHandle', [$this], ['Cms']);

				// Handle request
				$this->handle($requestUri);

				// Fire event before send response
				EventHelper::trigger('onBeforeSend', [$this], ['Cms']);

				// Send response
				$this->send();
			}

			// Check Session GC
			$probability = (int) Config::get('gcProbability', 1);
			$divisor     = (int) Config::get('gcDivisor', 100);
			$random      = $divisor * lcg_value();

			if ($probability > 0 && $random < $probability)
			{
				State::gc();
			}
		}
		catch (Throwable $e)
		{
			EventHelper::trigger('onHandleError', [$this, $e], ['Cms']);

			try
			{
				if (DEVELOPMENT_MODE || $user->is('super'))
				{
					State::setMark('exception', $e);
				}

				if ($this->response->isSent())
				{
					http_response_code($e->getCode());
				}
				else
				{
					$this->manualResponse(
						Uri::isClient('administrator') ? 'admin_error' : 'error',
						'show',
						[
							'code'      => $e->getCode(),
							'title'     => Text::_($e->getCode() . '-title'),
							'message'   => $e->getMessage(),
							'exception' => State::getMark('exception'),
						]
					);
				}
			}
			catch (Throwable $e2)
			{
				http_response_code($e2->getCode());
			}
		}
	}

	protected function manualResponse(string $controller, string $action, array $params = [])
	{
		try
		{
			$this->dispatcher->setNamespaceName(Constant::NAMESPACE_CONTROLLER);
			$this->dispatcher->setControllerName($controller);
			$this->dispatcher->setActionName($action);
			$this->dispatcher->setParams($params);
			$this->view->start();
			$this->dispatcher->dispatch();
			$this->view->render(
				$this->dispatcher->getControllerName(),
				$this->dispatcher->getActionName(),
				$this->dispatcher->getParams()
			);

			// View
			$this->view->finish();
			$this->response->setContent($this->view->getContent());
			$this->send();
		}
		catch (Throwable $e)
		{
			echo $e->getMessage();
			http_response_code($e->getCode());
		}
	}

	public function send()
	{
		if (!$this->response->isSent())
		{
			$content = $this->response->getContent();
			$assets  = Service::assets();

			// Build CSS
			ob_start();
			$assets->outputCss();
			$assets->outputInlineCss();
			$content = str_replace('<!--block:afterHead-->', ob_get_clean(), $content);

			// Build JS
			Text::scripts();
			ob_start();
			$assets->outputJs();
			$assets->outputInlineJs();
			$content = str_replace('<!--block:afterBody-->', ob_get_clean(), $content);

			if (Config::is('gzip')
				&& !ini_get('zlib.output_compression')
				&& ini_get('output_handler') != 'ob_gzhandler'
				&& extension_loaded('zlib')
			)
			{
				$supported = ['x-gzip' => 'gz', 'gzip' => 'gz', 'deflate' => 'deflate'];
				$encodings = array_intersect(array_map('trim', (array) explode(',', $_SERVER['HTTP_ACCEPT_ENCODING'] ?? [])), array_keys($supported));

				if (!empty($encodings))
				{
					foreach ($encodings as $encoding)
					{
						$gzData = gzencode($content, 4, ($supported[$encoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);

						// If there was a problem encoding the data just try the next encoding scheme.
						if (false !== $gzData)
						{
							$this->response->setHeader('Content-Encoding', $encoding);
							$this->response->setHeader('Vary', 'Accept-Encoding');
							$this->response->setHeader('X-Content-Encoded-By', 'HummingbirdCms');
							$content = $gzData;
							break;
						}
					}
				}
			}

			$this->response->setContent($content)->send();
		}
	}
}