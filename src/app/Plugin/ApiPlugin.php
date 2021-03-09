<?php

namespace App\Plugin;

use App\Factory\ApiApplication;
use App\Helper\Text;
use App\Helper\User;
use App\Traits\User as UserTrait;
use MaiVu\Php\Filter;

abstract class ApiPlugin extends Plugin
{
	/**
	 * @var ApiApplication
	 */

	protected $app;

	/**
	 * @var string
	 */
	protected $apiVersion = '1.0';

	/**
	 * @var string
	 */
	protected $apiPrefix;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var null|string
	 */
	protected $rawData = null;

	/**
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * @var string
	 */
	protected $requestUri = '';

	/**
	 * @var array
	 */

	protected $publicUris = [];


	/**
	 * @param ApiApplication $app
	 */

	use UserTrait;

	final public function onBootApi(ApiApplication $app)
	{
		$this->app        = $app;
		$this->requestUri = $this->app->request->getURI(true);
		$this->prefix     = Filter::toSlug($this->prefix);

		if (empty($this->prefix))
		{
			$this->prefix = $this->config['manifest.name'];
		}

		$this->apiPrefix = '/hb/io/api/' . $this->prefix . '/' . $this->apiVersion;
		static $requestData = null;

		if (null === $requestData)
		{
			$requestData   = $this->app->request->get() ?: [];
			$this->rawData = trim(file_get_contents('php://input'));

			if (0 === strpos($this->rawData, '{') || 0 === strpos($this->rawData, '['))
			{
				$requestData = array_merge($requestData, @json_decode($this->rawData, true) ?: []);
			}

			$this->data = $requestData;
		}

		if (0 === strpos($this->requestUri, $this->apiPrefix . '/'))
		{
			// Only route for the Api plugin which has a prefix
			// Route for login
			$loginUri    = $this->apiPrefix . '/user/login';
			$registerUri = $this->apiPrefix . '/user/register';
			$this->app->post($loginUri, [$this, 'login']);
			$this->app->post($registerUri, [$this, 'register']);
			$this->publicUris[] = $loginUri;
			$this->publicUris[] = $registerUri;

			if (($auth = $this->app->request->getHeader('HTTP_AUTHORIZATION')) && strpos($auth, 'Bearer ') === 0)
			{
				User::loginWithToken(str_replace('Bearer ', '', $auth));
			}

			$this->routes();
		}
		else
		{
			// Else detach this plugin
			$this->isDetached = true;
		}
	}

	abstract protected function routes();

	public function login($userName = null, $password = null)
	{
		$auth = $this->app->request->getBasicAuth();

		if (null === $userName)
		{
			$userName = $auth['username'] ?? null;
		}

		if (null === $password)
		{
			$password = $auth['password'] ?? null;
		}

		if ($user = User::login($userName, $password))
		{
			return $this->app->json($this->responseLoginData($user));
		}

		return $this->app->throw(Text::_('login-fail-message'), 403);
	}

	protected function responseLoginData($user)
	{
		$jsonData = [
			'id'       => (int) $user->id,
			'name'     => $user->name,
			'username' => $user->username,
			'email'    => $user->email,
			'token'    => md5($user->secret),
		];

		$this->callback('userLogged', [&$jsonData]);

		return $jsonData;
	}

	public function register()
	{
		$responseData = $this->handleUserRegister($this->data);

		if (false === $responseData)
		{
			return $this->app->throw(Text::_('access-denied'), 403);
		}

		if ($responseData['success'])
		{
			if ($responseData['userData']->active === 'Y')
			{
				return $this->app->json($this->responseLoginData($responseData['userData']));
			}

			unset($responseData['userData']);

			return $this->app->json($responseData);
		}

		return $this->app->throw(implode(PHP_EOL, $responseData['errorMessages']));
	}
}