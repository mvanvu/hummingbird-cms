<?php

namespace App\Plugin;

use App\Console\Fly\Socket;
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
	 * @var Socket
	 */

	protected $fly;

	/**
	 * @var Registry
	 */

	protected $data;


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

	final public function setData(Registry $data): SocketPlugin
	{
		$this->data = $data;

		return $this;
	}

	final public function onBootSocket(FlyApplication $app, Socket $fly)
	{
		$this->app = $app;
		$this->fly = $fly;
	}
}