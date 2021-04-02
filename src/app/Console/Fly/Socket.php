<?php

namespace App\Console\Fly;

use App\Console\Fly;
use App\Factory\FlyApplication;
use App\Helper\Constant;
use App\Helper\Event;
use App\Plugin\SocketPlugin;
use MaiVu\Php\Registry;
use Swoole\Http\Request;
use Swoole\Table;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class Socket implements Fly
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

	public function flap(FlyApplication $app, string $param = null)
	{
		$console           = $app->getConsole();
		$this->connections = new Table($console->getArgument('table-size', 1024, 'uint'));
		$this->connections->column('plugin', Table::TYPE_STRING, 60);
		$this->connections->create();
		$this->socket = new Server(
			$console->getArgument('host', '0.0.0.0'),
			$console->getArgument('port', 2053, 'uint')
		);

		Event::trigger('onBootSocket', [$app, $this], ['Socket']);
		$this->socket->on('open', function (Server $server, Request $request) {
			preg_match('#^/hb/io/ws/([a-zA-Z0-9_]+)#', $request->server['request_uri'], $matches);

			if (!empty($matches[1]))
			{
				$plugin  = $matches[1];
				$handler = Event::getHandlerByClass(Constant::getNamespacePlugin('Socket', $plugin));

				if ($handler instanceof SocketPlugin)
				{
					$this->connections[$request->fd] = ['plugin' => $plugin];
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
				$continue = $handler->setData(Registry::create($frame->data))
					->callback('onBeforeMessage', [$server, $frame]);

				// Skip if onBeforeMessage return false
				if (false !== $continue)
				{
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
			}
		});

		$this->socket->on('close', function (Server $server, int $fd) {
			unset($this->connections[$fd]);
		});

		// Start socket server
		$this->socket->start();
	}
}