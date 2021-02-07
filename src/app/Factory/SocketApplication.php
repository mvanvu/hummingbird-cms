<?php

declare(strict_types=1);

namespace App\Factory;

use App\Helper\Event;
use Throwable;

class SocketApplication extends CliApplication
{
	/**
	 * @var Socket
	 */
	protected $socket;

	public function getSocket()
	{
		return $this->socket;
	}

	public function execute()
	{
		try
		{
			$this->socket = new Socket;
			Event::trigger('onBootSocket', [$this], ['Socket']);

			// Run server
			$this->socket->run();
		}
		catch (Throwable $throwable)
		{
			$this->error($throwable->getMessage());
		}
	}
}