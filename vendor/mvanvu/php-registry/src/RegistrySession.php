<?php

namespace MaiVu\Php;

class RegistrySession
{
	/**
	 * @var boolean
	 */
	protected $isActive = false;

	/**
	 * @var Registry
	 */
	protected $data;

	public function __construct()
	{
		$this->data = new Registry;

		if ($this->isActive = (session_status() === PHP_SESSION_ACTIVE))
		{
			$this->data->map($_SESSION);
		}
	}

	public function start()
	{
		if (!$this->isActive && ($this->isActive = session_start()))
		{
			$this->data->map($_SESSION);
		}

		return $this;
	}

	public function getFlash($name, $default = null)
	{
		$value = $this->get($name, $default);
		$this->remove($name);

		return $value;
	}

	public function get($name, $default = null)
	{
		return $this->data->get($name, $default);
	}

	public function remove($name)
	{
		unset($this->data[$name]);

		return $this;
	}

	public function set($name, $value)
	{
		if ($this->isActive)
		{
			$this->data->set($name, $value);
		}

		return $this;
	}
}