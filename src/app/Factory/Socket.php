<?php

namespace App\Factory;

use App\Helper\Console;
use Ratchet\ComponentInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Http\HttpServerInterface;
use Ratchet\Http\OriginCheck;
use Ratchet\Http\Router;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\FlashPolicy;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\WebSocket\MessageComponentInterface as WsMessageComponentInterface;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\SecureServer;
use React\Socket\Server as Reactor;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Socket
{
	/**
	 * @var RouteCollection
	 */
	public $routes;

	/**
	 * @var IoServer
	 */
	public $flashServer;

	/**
	 * @var IoServer
	 */
	protected $server;

	/**
	 * The Host passed in construct used for same origin policy
	 * @var string
	 */
	protected $httpHost;

	/***
	 * The port the socket is listening
	 * @var int
	 */
	protected $port;

	/**
	 * @var int
	 */
	protected $routeCounter = 0;

	/**
	 * @param LoopFactory|null $loop
	 */
	public function __construct(LoopFactory $loop = null)
	{
		if (null === $loop)
		{
			$loop = LoopFactory::create();
		}

		$console        = Console::getInstance();
		$address        = $console->getArgument('address', '127.0.0.1');
		$this->httpHost = $console->getArgument('host', 'localhost');
		$this->port     = $console->getArgument('port', 8080, 'int');
		$crtPath        = $console->getArgument('sslCert');
		$keyPath        = $console->getArgument('sslKey');
		$socket         = new Reactor($address . ':' . $this->port, $loop);

		if ($crtPath && $keyPath)
		{
			$socket = new SecureServer(
				$socket,
				$loop,
				[
					'local_cert'        => $crtPath,
					'local_pk'          => $keyPath,
					'allow_self_signed' => false,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				]
			);
		}

		$this->routes = new RouteCollection;
		$this->server = new IoServer(
			new HttpServer(
				new Router(
					new UrlMatcher(
						$this->routes,
						new RequestContext
					)
				)
			),
			$socket,
			$loop
		);
		$policy       = new FlashPolicy;
		$policy->addAllowedAccess($this->httpHost, 80);
		$policy->addAllowedAccess($this->httpHost, $this->port);

		if (80 == $this->port)
		{
			$flashUri = '0.0.0.0:843';
		}
		else
		{
			$flashUri = 8843;
		}

		$flashSock         = new Reactor($flashUri, $loop);
		$this->flashServer = new IoServer($policy, $flashSock);
	}

	/**
	 * Add an endpoint/application to the server
	 *
	 * @param string             $path           The URI the client will connect to
	 * @param ComponentInterface $controller     Your application to server for the route. If not specified, assumed to be for a WebSocket
	 * @param array              $allowedOrigins An array of hosts allowed to connect (same host by default), ['*'] for any
	 * @param null               $httpHost       Override the $httpHost variable provided in the __construct
	 *
	 * @return ComponentInterface|WsServer
	 */
	public function route($path, ComponentInterface $controller, array $allowedOrigins = [], $httpHost = null)
	{
		if ($controller instanceof HttpServerInterface || $controller instanceof WsServer)
		{
			$decorated = $controller;
		}
		elseif ($controller instanceof WampServerInterface)
		{
			$decorated = new WsServer(new WampServer($controller));
			$decorated->enableKeepAlive($this->server->loop);
		}
		elseif ($controller instanceof MessageComponentInterface || $controller instanceof WsMessageComponentInterface)
		{
			$decorated = new WsServer($controller);
			$decorated->enableKeepAlive($this->server->loop);
		}
		else
		{
			$decorated = $controller;
		}

		if ($httpHost === null)
		{
			$httpHost = $this->httpHost;
		}

		$allowedOrigins = array_values($allowedOrigins);

		if (0 === count($allowedOrigins))
		{
			$allowedOrigins[] = $httpHost;
		}

		if ('*' !== $allowedOrigins[0])
		{
			$decorated = new OriginCheck($decorated, $allowedOrigins);
		}

		// Allow origins in flash policy server
		if (empty($this->flashServer) === false)
		{
			foreach ($allowedOrigins as $allowedOrgin)
			{
				$this->flashServer->app->addAllowedAccess($allowedOrgin, $this->port);
			}
		}

		$this->routes->add('rr-' . ++$this->routeCounter, new Route($path, ['_controller' => $decorated], ['Origin' => $this->httpHost], [], $httpHost, [], ['GET']));

		return $decorated;
	}

	/**
	 * Run the server by entering the event loop
	 */
	public function run()
	{
		$this->server->run();
	}
}
