<?php

namespace App\Plugin;

use App\Factory\SocketApplication;
use App\Helper\Date;
use App\Helper\State;
use App\Helper\User;
use App\Mvc\Model\SocketData;
use App\Mvc\Model\User as UserModel;
use App\Traits\Hooker;
use Exception;
use MaiVu\Php\Registry;
use Ratchet\App as SocketApp;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;

class SocketPlugin extends Plugin implements MessageComponentInterface
{
	/**
	 * @var SocketApplication
	 */

	protected $app;

	/**
	 * @var SplObjectStorage
	 */
	protected $clients;

	/**
	 * @var SocketApp
	 */
	protected $socket;

	/**
	 * @var ConnectionInterface
	 */

	protected $client = null;

	/**
	 * @var Registry;
	 */

	protected $message = null;

	/**
	 * @var string
	 */

	protected $storeId = '';

	/**
	 * @var string
	 */

	protected $storeContext = '';

	use Hooker;

	final public function onBootSocket(SocketApplication $app)
	{
		$this->app     = $app;
		$this->clients = new SplObjectStorage;
		$this->socket  = $this->app->getSocket();
		$this->routes();
	}

	protected function routes()
	{
		$this->defaultRoute();
	}

	protected function defaultRoute()
	{
		$this->socket->route('/websocket/' . strtolower($this->config->get('manifest.name')), $this, ['*']);
	}

	public function onOpen(ConnectionInterface $connection)
	{
		if ($this->beforeAttach($connection))
		{
			$this->clients->attach($connection);
		}
	}

	protected function beforeAttach(ConnectionInterface $connection): bool
	{
		return true;
	}

	public function onClose(ConnectionInterface $connection)
	{
		$this->clients->detach($connection);
	}

	public function onError(ConnectionInterface $connection, Exception $e)
	{
		$this->app->error($e->getMessage());
		$connection->close();
	}

	final public function onMessage(ConnectionInterface $from, $message)
	{
		/** @var ConnectionInterface $client */
		$this->message = new Registry($message);
		$this->storeId = md5($from->resourceId . ':' . $this->message->toString() . ':' . time());

		foreach ($this->clients as $client)
		{
			if (!isset($client->cmsUser))
			{
				if ($auth = $this->message->get('headers.Authorization'))
				{
					if (strpos($auth, 'Bearer ') === 0)
					{
						$entity = UserModel::findFirst(
							[
								'conditions' => 'MD5(secret) = :token: AND active = :yes:',
								'bind'       => [
									'token' => $auth,
									'yes'   => 'Y',
								],
							]
						);
					}
					else
					{
						$entity = State::getById($auth)->get('site.user.id');
					}
				}

				$client->cmsUser = User::getInstance($entity ?? 0);
			}

			$this->client = $client;
			$this->callback('message');

			if ($from->resourceId != $client->resourceId)
			{
				$this->callback('broadcast');
			}
		}

		if ($this->storeContext)
		{
			$this->store();
		}
	}

	protected function store()
	{
		return (new SocketData)
			->assign(
				[
					'id'        => $this->storeId,
					'context'   => $this->storeContext,
					'message'   => $this->message->toString(),
					'createdBy' => $this->client->cmsUser->id,
					'createdAt' => Date::now('UTC')->toSql(),
				]
			)
			->create();
	}
}