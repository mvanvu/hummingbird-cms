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

		foreach (($_SERVER['argv'] ?? []) as $arg)
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

	public function getArgument(string $name, string $default = null, $filter = null)
	{
		return $this->arguments->get($name, $default, $filter);
	}

	public function hasArgument(string $name): bool
	{
		return $this->arguments->has($name);
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
		fwrite(STDERR, PHP_EOL . $message);
	}

	public function out(string $message)
	{
		fwrite(STDOUT, PHP_EOL . $message);
	}

	public function executeNow(...$args)
	{
		return $this->execute(false, ...$args);
	}

	public function execute($queue, ...$args)
	{
		$cmd = ($_SERVER['_'] ?? 'php') . ' ' . BASE_PATH . '/fly';

		if ($args)
		{
			$cmd .= ' ' . implode(' ', $args);
		}

		$cmd .= ' > /dev/null 2>&1' . ($queue ? ' &' : '');

		return shell_exec($cmd);
	}

	public function executeQueue(...$args)
	{
		return $this->execute(true, ...$args);
	}

	public function composer(string $command, string $pathToJson)
	{
		Queue::add(
			Composer::class,
			[
				'command'    => $command,
				'pathToJson' => $pathToJson,
			]
		);
	}
}