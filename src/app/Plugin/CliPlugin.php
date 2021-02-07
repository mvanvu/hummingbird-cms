<?php

namespace App\Plugin;

use App\Factory\CliApplication;

abstract class CliPlugin extends Plugin
{
	/**
	 * @var CliApplication
	 */

	protected $app;

	final public function onBootCli(CliApplication $app)
	{
		$this->app = $app;
		$this->handle();
	}

	abstract public function handle();

	public function out(string $message, $handle = STDOUT)
	{
		return $this->app->out($message, $handle);
	}
}