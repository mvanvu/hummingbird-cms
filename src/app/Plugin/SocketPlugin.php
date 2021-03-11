<?php

namespace App\Plugin;

use App\Factory\FlyApplication;
use App\Helper\User;
use MaiVu\Php\Registry;

class SocketPlugin extends Plugin
{
	/**
	 * @var FlyApplication
	 */

	protected $app;

	/**
	 * @var Registry
	 */

	protected $data;

	/**
	 * @var Registry
	 */

	protected $session;


	/**
	 * @var User | null
	 */

	protected $auth;

	/**
	 * @var string
	 */

	protected $storeId = '';

	/**
	 * @var string
	 */

	protected $storeContext = '';

	final public function reset(): SocketPlugin
	{
		$this->auth    = null;
		$this->data    = null;
		$this->session = null;

		return $this;
	}

	final public function setData(Registry $data): SocketPlugin
	{
		$this->data = $data;

		return $this;
	}

	final public function setSession(Registry $session): SocketPlugin
	{
		$this->session = $session;

		return $this;
	}

	final public function setAuth(User $user): SocketPlugin
	{
		$this->auth = $user;

		return $this;
	}

	final public function onBootSocket(FlyApplication $app)
	{
		$this->app = $app;
	}
}