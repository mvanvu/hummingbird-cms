<?php

declare(strict_types=1);

namespace App\Factory;

use App\Helper\Constant;
use App\Helper\Database;
use App\Helper\Event;
use App\Helper\Service;
use App\Helper\State;
use App\Helper\User;
use App\Plugin\SocketPlugin;
use MaiVu\Php\Registry;
use Swoole\Http\Request;
use Swoole\Table;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Throwable;

class SocketApplication extends CliApplication
{
	/**
	 * @var Table
	 */
	protected $connections;

	/**
	 * @var Server
	 */
	protected $socket;

	public function getSocket()
	{
		return $this->socket;
	}

	public function getConnections()
	{
		return $this->connections;
	}

	public function execute()
	{
		try
		{
			$this->connections = new Table($this->console->getArgument('table-size', 1024));
			$this->connections->column('plugin', Table::TYPE_STRING, 60);
			$this->connections->column('auth', Table::TYPE_STRING, 191);
			$this->connections->create();
			$this->socket = new Server(
				$this->console->getArgument('host', '0.0.0.0'),
				$this->console->getArgument('port', 2053, 'uint')
			);

			Event::trigger('onBootSocket', [$this], ['Socket']);
			$this->socket->on('open', function (Server $server, Request $request) {
				preg_match('#^/websocket/([a-zA-Z0-9_]+)#', $request->server['request_uri'], $matches);

				if (!empty($matches[1]))
				{
					$plugin  = $matches[1];
					$handler = Event::getHandlerByClass(Constant::getNamespacePlugin('Socket', $plugin));

					if ($handler instanceof SocketPlugin)
					{
						$this->connections[$request->fd] = ['plugin' => $plugin, 'auth' => 'HB_SESSION_ID:' . $request->cookie['HB_SESSION_ID'] ?? ''];
						$handler->callback('onOpen', [$server, $request]);
					}
				}
			});

			$this->socket->on('message', function (Server $server, Frame $frame) {

				if (isset($this->connections[$frame->fd])
					&& ($handler = Event::getHandlerByClass(Constant::getNamespacePlugin('Socket', $this->connections[$frame->fd]['plugin'])))
					&& $handler instanceof SocketPlugin
				)
				{
					$handler->reset()->setData(Registry::create($frame->data));
					$userId = 0;

					if ($auth = $this->connections[$frame->fd]['auth'])
					{
						if (0 === strpos($auth, 'HB_SESSION_ID:')
							&& $session = State::getById(substr($auth, 14))
						)
						{
							$userId = $session->get('site.user.id', 0);
							$handler->setSession($session);
						}
						elseif (0 === strpos($auth, 'token:'))
						{
							$userId = Service::db()->fetchColumn('SELECT id FROM ' . Database::table('users') . ' WHERE active = \'Y\' AND MD5(secret) = ?', [substr($auth, 6)])['id'] ?? 0;
						}
					}

					$handler->setAuth(User::getInstance((int) $userId));
					$handler->callback('onBeforeMessage', [$server, $frame]);

					foreach ($this->connections as $fd => $connection)
					{
						$handler->callback('onMessage', [$server, $fd]);

						if ($fd == $frame->fd)
						{
							$handler->callback('onSelf', [$server, $fd]);
						}
						else
						{
							$handler->callback('onBroadcast', [$server, $fd]);
						}
					}

					$handler->callback('onAfterMessage', [$server, $frame]);
				}
			});

			$this->socket->on('close', function (Server $server, int $fd) {
				unset($this->connections[$fd]);
			});

			// Start socket server
			$this->socket->start();
		}
		catch (Throwable $throwable)
		{
			$this->error($throwable->getMessage());
		}
	}
}