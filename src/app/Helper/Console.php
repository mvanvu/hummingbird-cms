<?php

namespace App\Helper;

use App\Queue\Composer;
use MaiVu\Php\Registry;

class Console
{
	/**
	 * @var Registry
	 */
	protected $arguments;

	protected function __construct()
	{
		$this->arguments = Registry::create();

		if ($args = ($_SERVER['argv'] ?? []))
		{
			// Strip the application name
			array_shift($args);

			foreach ($args as $arg)
			{
				$arg = trim($arg, '-"\'');

				if (false === strpos($arg, '='))
				{
					$this->arguments->set($arg, null);
				}
				else
				{
					list($name, $value) = explode('=', $arg, 2);
					$this->arguments->set(trim($name), trim($value));
				}
			}
		}
	}

	public static function getInstance(): Console
	{
		static $instance = null;

		if (null === $instance)
		{
			$instance = new Console;
		}

		return $instance;
	}

	public function getArguments(): Registry
	{
		return $this->arguments;
	}

	public function hasArgument(string $name): bool
	{
		return $this->arguments->has($name);
	}

	public function runCallbacks(string $callbacks = null)
	{
		$callbacks = explode(',', $callbacks ?? $this->getArgument('callback'));

		foreach ($callbacks as $callback)
		{
			if (is_callable($callback))
			{
				if ($arguments = $this->getArgument('args[' . $callback . ']', ''))
				{
					$arguments = explode(',', $arguments);
				}
				else
				{
					$arguments = [];
				}

				call_user_func_array($callback, $arguments);
			}
		}

		if ($eval = $this->getArgument('eval'))
		{
			eval($eval);
		}
	}

	public function getArgument(string $name, string $default = null, $filter = null)
	{
		return $this->arguments->get($name, $default, $filter);
	}

	public function match(string $name)
	{
		foreach ($this->arguments->toArray() as $k => $v)
		{
			$pattern = str_replace(['\*', '\^', '\$'], ['.*', '^', '$'], preg_quote($name, '#'));

			if ($name === $k || preg_match('#' . $pattern . '\z#u', $k) === 1)
			{
				return [$k, $v];
			}
		}

		return false;
	}

	public function error(string $message)
	{
		fwrite(STDERR, $message);
	}

	public function outLn(string $message)
	{
		$this->out($message . PHP_EOL);
	}

	public function out(string $message)
	{
		fwrite(STDOUT, PHP_EOL . $message);
	}

	public function executeNow(string $args = null)
	{
		return $this->execute($args, false);
	}

	public function execute(string $command = null, bool $background = true)
	{
		$cmd = ($_SERVER['_'] ?? 'php') . ' ' . BASE_PATH . '/fly';

		if ($command)
		{
			$cmd .= ' ' . $command;
		}

		$cmd .= ' > /dev/null 2>&1';

		if ($background)
		{
			$cmd .= ' &';
		}

		return shell_exec($cmd);
	}

	public function composer($commands, string $pathToJson)
	{
		Queue::add(
			Composer::class,
			[
				'commands'   => $commands,
				'pathToJson' => $pathToJson,
			]
		);
	}
}