<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use MaiVu\Php\Filter;
use MaiVu\Hummingbird\Lib\Factory;

class Uri
{
	/** @var array */
	protected $vars = [];

	/** @var string */
	protected $baseUri = BASE_URI;

	/** @var Uri */
	protected static $active;

	public function __construct(array $vars = null, $merge = true)
	{
		if (is_array($vars))
		{
			if ($merge)
			{
				$vars = array_merge(self::extract(), $vars);
			}
		}
		else
		{
			$vars = self::extract();
		}

		if (!isset($vars['format']))
		{
			$vars['format'] = Factory::getService('dispatcher')->getParam('format', ['string', 'trim'], '');
		}

		if (isset($_SERVER['QUERY_STRING']))
		{
			parse_str($_SERVER['QUERY_STRING'], $query);

			if (isset($query['_url']))
			{
				unset($query['_url']);
			}

			$vars['query'] = $query;
		}

		$this->vars = $vars;
		$this->setBaseUri($vars['uri']);
	}

	public function setBaseUri($uri)
	{
		$this->baseUri = self::clean($uri);

		return $this;
	}

	public function getBaseUri()
	{
		return $this->baseUri;
	}

	public static function getHost()
	{
		static $host = null;

		if (null === $host)
		{
			// Determine if the request was over SSL (HTTPS).
			if (isset($_SERVER['HTTPS'])
				&& !empty($_SERVER['HTTPS'])
				&& (strtolower($_SERVER['HTTPS']) != 'off')
			)
			{
				$https = 's://';
			}
			elseif ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
				!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
				(strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'http')))
			{
				$https = 's://';
			}
			else
			{
				$https = '://';
			}

			$host = 'http' . $https . $_SERVER['HTTP_HOST'];
		}

		return $host;
	}

	public static function getActive($fromServer = false)
	{
		if ($fromServer)
		{
			return static::fromServer();
		}

		if (!self::$active instanceof Uri)
		{
			self::$active = static::getInstance();
		}

		return self::$active;
	}

	public static function getInstance(array $vars = null, $merge = true)
	{
		return new Uri($vars, $merge);
	}

	public function getVars()
	{
		return $this->vars;
	}

	public function setVar($name, $value)
	{
		$this->vars[$name] = $value;

		return $this;
	}

	public function getVar($name, $default = null)
	{
		return isset($this->vars[$name]) ? $this->vars[$name] : $default;
	}

	public function setQuery($name, $value)
	{
		$this->vars['query'][$name] = $value;

		return $this;
	}

	public function getQuery($name, $default = null)
	{
		return isset($this->vars['query'][$name]) ? $this->vars['query'][$name] : $default;
	}

	public function appendQuery($name, $value)
	{
		$var = $this->getQuery($name, null);

		if (null === $var)
		{
			$var = $value;
		}
		else
		{
			$vars = explode('|', $var);

			if (!in_array($value, $vars))
			{
				$vars[] = $value;
			}

			$var = join('|', Filter::clean($vars, 'unique'));
		}

		$this->setQuery($name, $var);

		return $this;
	}

	public function isInternal()
	{
		return $this->vars['client'] !== null;
	}

	public static function extract($baseUri = null)
	{
		if (null === $baseUri)
		{
			$baseUri = BASE_URI;
		}

		$baseUri = trim($baseUri, '/');
		static $vars = [];

		if (!isset($vars[$baseUri]))
		{
			$results = [
				'uri'      => $baseUri,
				'host'     => self::getHost(),
				'query'    => [],
				'client'   => null,
				'language' => null,
				'format'   => null,
			];

			if (strpos($baseUri, ADMIN_URI_PREFIX) === 0)
			{
				$results['client'] = 'administrator';
				$baseUri           = ltrim(preg_replace('/^' . preg_quote(ADMIN_URI_PREFIX, '/') . '/', '', $baseUri), '/');
			}
			else
			{
				$results['client'] = 'site';
			}

			$parts = explode('/', $baseUri);

			if (isset($parts[0]))
			{
				if (Language::hasSef($parts[0]))
				{
					$results['language'] = $parts[0];
					array_shift($parts);

					if (isset($parts[0]) && $parts[0] === 'raw')
					{
						$results['format'] = 'raw';
						array_shift($parts);
					}
				}
				elseif ($parts[0] === 'raw')
				{
					$results['format'] = 'raw';
					array_shift($parts);
				}
			}

			$results['uri'] = implode('/', $parts);
			$vars[$baseUri] = $results;
		}

		return $vars[$baseUri];
	}

	public static function fromServer()
	{
		static $uriServer = null;

		if (null === $uriServer)
		{
			if (isset($_SERVER['HTTPS'])
				&& !empty($_SERVER['HTTPS'])
				&& (strtolower($_SERVER['HTTPS']) != 'off')
			)
			{
				$https = 's://';
			}
			elseif ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
				!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
				(strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'http')))
			{
				$https = 's://';
			}
			else
			{
				$https = '://';
			}

			if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI']))
			{
				$uri = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}
			else
			{
				$uri = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

				if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
				{
					$uri .= '?' . $_SERVER['QUERY_STRING'];
				}
			}

			$uriServer = str_replace(["'", '"', '<', '>'], ['%27', '%22', '%3C', '%3E'], $uri);
		}

		return $uriServer;
	}

	public static function isClient($name)
	{
		return self::getActive()->getVar('client') === $name;
	}

	public static function fromUrl($url)
	{
		if ($parts = self::parseUrl($url))
		{
			$vars = [];
			$host = [];

			if (isset($parts['query']))
			{
				if (strpos($parts['query'], '&amp;'))
				{
					$parts['query'] = str_replace('&amp;', '&', $parts['query']);
				}

				parse_str($parts['query'], $vars['query']);
			}

			if (isset($result['scheme']))
			{
				$host[] = $parts['scheme'] . ':/';
			}

			if (isset($result['host']))
			{
				$host[] = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
			}

			$host = join('/', $host);
			$vars = static::extract(isset($parts['path']) ? $parts['path'] : '');

			if (!empty($host) && $host !== static::getHost())
			{
				$vars['host']   = $host;
				$vars['client'] = null;
			}

			return static::getInstance($vars, false);
		}

		return false;
	}

	public function toString($query = null, $full = false)
	{
		$theUri = ($full ? $this->getVar('host') : '') . '/';

		if ($this->getVar('client') === 'administrator')
		{
			$theUri .= $this->getVar('adminPrefix', ADMIN_URI_PREFIX) . '/';
		}

		if (($language = $this->getVar('language'))
			&& Language::hasSef($language)
			&& $language !== Language::getDefault()->get('locale.sef')
		)
		{
			$theUri .= $language . '/';
		}

		if ($format = $this->getVar('format'))
		{
			$theUri .= $format . '/';
		}

		if ($uri = $this->getBaseUri())
		{
			$theUri .= $uri . '/';
		}

		if (is_array($query))
		{
			$query = array_merge($this->vars['query'], $query);
		}
		elseif (false !== $query)
		{
			$query = $this->vars['query'];
		}

		if (!empty($query))
		{
			$theUri .= '?' . http_build_query($query);
		}

		$theUri = str_replace(["'", '"', '<', '>'], ['%27', '%22', '%3C', '%3E'], rtrim($theUri, '/'));

		return $theUri ?: '/';
	}

	public function __toString()
	{
		return (string) $this->toString();
	}

	public static function clean($uri)
	{
		return preg_replace(['#/+#', '#^/+#', '#/+$#'], ['/', '', ''], $uri);
	}

	public static function route($baseUri = '', $query = false)
	{
		$baseUri = self::clean($baseUri);
		static $routes = [];

		if (!isset($routes[$baseUri]))
		{
			$routes[$baseUri] = self::getInstance(['uri' => $baseUri]);
		}

		return $routes[$baseUri]->toString($query);
	}

	public static function isHome()
	{
		static $isHome = null;

		if (null === $isHome)
		{
			$isHome = self::clean(BASE_URI) === self::clean(self::getBaseUriPrefix());
		}

		return $isHome;
	}

	public static function getBaseUriPrefix()
	{
		$uriVars = self::getActive()->getVars();

		if ($uriVars['client'] === 'site')
		{
			if (isset($uriVars['language']))
			{
				return '/' . $uriVars['language'];
			}

			return '';
		}

		if (isset($uriVars['language']))
		{
			return '/' . ADMIN_URI_PREFIX . '/' . $uriVars['language'];
		}

		return '/' . ADMIN_URI_PREFIX;
	}

	public static function home()
	{
		return self::route('/');
	}

	public function routeTo($baseUri)
	{
		$uri = clone $this;
		$uri->setBaseUri($uri->getBaseUri() . '/' . $baseUri);

		return $uri->toString(false);
	}

	public static function parseUrl($url)
	{
		$result = false;

		// Build arrays of values we need to decode before parsing
		$entities     = ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D'];
		$replacements = ['!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "$", ",", "/", "?", "#", "[", "]"];

		// Create encoded URL with special URL characters decoded so it can be parsed
		// All other characters will be encoded
		$encodedURL = str_replace($entities, $replacements, urlencode($url));

		// Parse the encoded URL
		$encodedParts = parse_url($encodedURL);

		// Now, decode each value of the resulting array
		if ($encodedParts)
		{
			foreach ($encodedParts as $key => $value)
			{
				$result[$key] = urldecode(str_replace($replacements, $entities, $value));
			}
		}

		return $result;
	}
}