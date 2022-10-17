<?php

namespace MaiVu\Php;

use ArrayAccess;

class Registry implements ArrayAccess
{
	/**
	 * @var string
	 */
	protected $separator = '.';

	/**
	 * @var array
	 */
	protected $data = [];

	public function __construct($data = [], string $separator = '.')
	{
		$this->separator = $separator;
		$this->setData($data);
	}

	public function setData($data): Registry
	{
		$this->data = $this->parse($data);

		return $this;
	}

	public function parse($data): array
	{
		return static::parseData($data);
	}

	public static function parseData($data): array
	{
		if ($data instanceof Registry)
		{
			$data = $data->toArray();
		}
		elseif (is_object($data))
		{
			$data = is_callable([$data, 'toArray']) ? $data->toArray() : (array) $data;
		}
		elseif (is_string($data))
		{
			if (strpos($data, '{') === 0 || strpos($data, '[') === 0)
			{
				$data = json_decode($data, true) ?: [];
			}
			elseif (is_file($data))
			{
				preg_match('/\.([a-z]+)$/i', $data, $matches);

				if (!empty($matches[1]))
				{
					switch ($matches[1])
					{
						case 'php':
							$data = include $data;
							break;

						case 'json':
							$data = json_decode(file_get_contents($data), true) ?: [];
							break;

						case 'ini':
							$data = parse_ini_file($data);
							break;
					}
				}
			}
		}

		if (empty($data))
		{
			return [];
		}

		return (array) $data;
	}

	public function toArray(): array
	{
		return $this->data;
	}

	public static function request(): Registry
	{
		static $request = null;

		if (null === $request)
		{
			$request = new Registry(
				[
					'get'     => &$_GET,
					'post'    => &$_POST,
					'request' => &$_REQUEST,
					'server'  => &$_SERVER,
					'files'   => &$_FILES,
				]
			);
		}

		return $request;
	}

	public static function session(): RegistrySession
	{
		static $session = null;

		if (null === $session)
		{
			$session = new RegistrySession;
		}

		return $session;
	}

	public function clear(): Registry
	{
		$this->data = [];

		return $this;
	}

	public function map(array &$data): Registry
	{
		$this->data = &$data;

		return $this;
	}

	public function isEmpty(): bool
	{
		return empty($this->data);
	}

	public function merge($data, $recursive = false): Registry
	{
		$data = $this->parse($data);

		if ($recursive)
		{
			$this->data = array_merge_recursive($this->data, $data);
		}
		else
		{
			$this->data = array_merge($this->data, $data);
		}

		return $this;
	}

	public function __toString()
	{
		return $this->toString();
	}

	public function toString()
	{
		return json_encode($this->data);
	}

	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset) : bool
	{
		return $this->has($offset);
	}

	public function has($path): bool
	{
		if (false === strpos($path, $this->separator))
		{
			return array_key_exists($path, $this->data);
		}

		$keys = explode($this->separator, $path);
		$data = $this->data;

		foreach ($keys as $key)
		{
			if (!array_key_exists($key, $data))
			{
				return false;
			}

			$data = $data[$key];
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset): mixed
	{
		return $this->get($offset);
	}

	public function get($path, $defaultValue = null, $filter = null)
	{
		if (false === strpos($path, $this->separator))
		{
			$data = array_key_exists($path, $this->data) ? $this->data[$path] : $defaultValue;
		}
		else
		{
			$keys = explode($this->separator, $path);
			$data = $this->data;

			foreach ($keys as $key)
			{
				if (!isset($data[$key]))
				{
					return $defaultValue;
				}

				$data = $data[$key];
			}
		}

		if ($filter)
		{
			$data = Filter::clean($data, $filter);
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value): void
	{
		$this->set($offset, $value);
	}

	public function set($path, $value, $filter = null)
	{
		if ($filter)
		{
			$value = Filter::clean($value, $filter);
		}

		if (false === strpos($path, $this->separator))
		{
			$this->data[$path] = $value;
		}
		else
		{
			$keys = explode($this->separator, $path);
			$data = &$this->data;

			foreach ($keys as $key)
			{
				if (!isset($data[$key]))
				{
					$data[$key] = [];
				}

				$data = &$data[$key];
			}

			$data = $value;
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset): void
	{
		if (false !== strpos($offset, $this->separator))
		{
			$offsets = explode($this->separator, $offset);
			$data    = &$this->data;
			$endKey  = array_pop($offsets);

			foreach ($offsets as $offset)
			{
				if (!isset($data[$offset]))
				{
					return;
				}

				$data = &$data[$offset];
			}

			unset($data[$endKey]);
		}
		else
		{
			unset($this->data[$offset]);
		}
	}

	public function __get($name)
	{
		$data = $this->get($name);

		if (is_array($data))
		{
			return Registry::create($data, $this->separator);
		}

		return $data;
	}

	public static function create($data = null, $separator = '.'): Registry
	{
		return new Registry($data, $separator);
	}
}