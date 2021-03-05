<?php

namespace App\Helper;

use MaiVu\Php\Filter;

class Uri
{
	/**
	 * @var Uri
	 */
	protected static $active;

	/**
	 * @var array
	 */
	protected $vars = [];

	/**
	 * @var string
	 */
	protected $baseUri = BASE_URI;

	public function __construct(array $vars = null, $merge = true)
	{
		if (is_array($vars))
		{
			if ($merge)
			{
				$vars = array_merge(static::extract(), $vars);
			}
		}
		else
		{
			$vars = static::extract();
		}

		if (!isset($vars['format']))
		{
			$vars['format'] = Service::dispatcher()->getParam('format', ['string', 'trim'], '');
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

	public static function extract($baseUri = BASE_URI)
	{
		$baseUri = trim($baseUri, '/');
		static $vars = [];

		if (!isset($vars[$baseUri]))
		{
			$results = [
				'uri'      => $baseUri,
				'host'     => static::getHost(),
				'query'    => [],
				'client'   => null,
				'language' => null,
				'format'   => null,
			];

			if (strpos($baseUri . '/', ADMIN_URI_PREFIX . '/') === 0)
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

	public static function getHost()
	{
		static $host = null;

		if (null === $host)
		{
			$host = static::getCurrentHttpSchema() . $_SERVER['HTTP_HOST'];
		}

		return $host;
	}

	public static function getCurrentHttpSchema()
	{
		static $http = null;

		if (null === $http)
		{
			$http = 'http';

			// Determine if the request was over SSL (HTTPS).
			if ((isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off'))
				|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) !== 'http')
			)
			{
				$http .= 's';
			}

			$http .= '://';
		}

		return $http;
	}

	public static function isClient($name)
	{
		return static::getClient() === $name;
	}

	public static function getClient()
	{
		if (IS_CLI)
		{
			return Console::getInstance()->hasArgument('socket') ? 'socket' : 'cli';
		}

		if (IS_API)
		{
			return 'api';
		}

		return static::getActive()->getVar('client');
	}

	public function getVar($name, $default = null)
	{
		return isset($this->vars[$name]) ? $this->vars[$name] : $default;
	}

	public static function getActive($pathOnly = false)
	{
		if (!static::$active instanceof Uri)
		{
			static::$active = static::fromUrl(static::fromServer()) ?: static::getInstance();
		}

		if ($pathOnly)
		{
			return static::$active->toPath();
		}

		return static::$active;
	}

	public static function fromUrl($url)
	{
		if ($parts = static::parseUrl($url))
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

			if (isset($parts['scheme']))
			{
				$host[] = $parts['scheme'] . ':/';
			}

			if (isset($parts['host']))
			{
				$host[] = $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
			}

			$host = implode('/', $host);
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

	public static function getInstance(array $vars = null, $merge = true)
	{
		return new Uri($vars, $merge);
	}

	public static function fromServer()
	{
		static $uriServer = null;

		if (null === $uriServer)
		{
			$http = static::getCurrentHttpSchema();

			if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI']))
			{
				$uri = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}
			else
			{
				$uri = $http . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

				if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
				{
					$uri .= '?' . $_SERVER['QUERY_STRING'];
				}
			}

			$uriServer = str_replace(["'", '"', '<', '>'], ['%27', '%22', '%3C', '%3E'], $uri);
		}

		return $uriServer;
	}

	public function toPath(): string
	{
		return trim($this->toString(false, false), '/#');
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

	public function getBaseUri()
	{
		return $this->baseUri;
	}

	public function setBaseUri($uri)
	{
		$this->baseUri = static::clean($uri);

		return $this;
	}

	public static function isHome()
	{
		static $isHome = null;

		if (null === $isHome)
		{
			$isHome = static::clean(BASE_URI) === static::clean(static::getBaseUriPrefix());
		}

		return $isHome;
	}

	public static function clean($uri)
	{
		return preg_replace(['#/+#', '#^/+#', '#/+$#'], ['/', '', ''], $uri);
	}

	public static function getBaseUriPrefix()
	{
		$uriVars = static::getActive()->getVars();

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

	public function getVars()
	{
		return $this->vars;
	}

	public static function back()
	{
		if ($referer = Service::request()->getServer('HTTP_REFERER'))
		{
			$uri = Uri::fromUrl($referer);

			if ($uri->isInternal())
			{
				$redirectUri = $uri->toString(true);
			}
		}

		return Uri::redirect($redirectUri ?? Uri::home());
	}

	public function isInternal()
	{
		return $this->getVar('client') !== null && $this->getVar('host') === static::getHost();
	}

	public static function redirect($url, $status = 302)
	{
		$response = Service::response();

		if ($response->isSent())
		{
			@ob_clean();
			$lang = Text::_('locale.code');
			echo <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="utf-8"/>
    <title>Redirecting...</title>
    <script>document.location.href='{$url}';</script>
</head>
<body></body>
</html>
HTML;
			exit(0);
		}

		return $response->redirect($url, true, $status)->send();
	}

	public static function home()
	{
		return static::route('/');
	}

	public static function route($baseUri = '', $query = false, $full = false)
	{
		$baseUri = static::clean($baseUri);
		static $routes = [];

		if (!isset($routes[$baseUri]))
		{
			$routes[$baseUri] = static::getInstance(['uri' => $baseUri]);
		}

		return $routes[$baseUri]->toString($query, $full);
	}

	public static function is(string $stringPath): bool
	{
		$search  = [];
		$replace = [];
		preg_match_all('/~([^~]+)~/', $stringPath, $matches);

		if (!empty($matches[1]))
		{
			foreach ($matches[1] as $regex)
			{
				$search[]  = '~' . preg_quote($regex, '#') . '~';
				$replace[] = $regex;
			}
		}

		$search[]    = '\*';
		$replace[]   = '.*';
		$search[]    = '\$';
		$replace[]   = '$';
		$stringPath  = ltrim(rawurldecode($stringPath), '/');
		$currentPath = ltrim(Uri::getActive()->toString(true), '/');
		$pattern     = str_replace($search, $replace, preg_quote($stringPath, '#'));

		return $stringPath === $currentPath || preg_match('#' . $pattern . '\z#u', $currentPath) === 1;
	}

	public function setVar($name, $value)
	{
		$this->vars[$name] = $value;

		return $this;
	}

	public function appendQuery($name, $value, $separator = '|')
	{
		$var = $this->getQuery($name, null);

		if (null === $var)
		{
			$var = $value;
		}
		else
		{
			$vars = explode($separator, $var);

			if (!in_array($value, $vars))
			{
				$vars[] = $value;
			}

			$var = implode($separator, Filter::clean($vars, 'unique'));
		}

		$this->setQuery($name, $var);

		return $this;
	}

	public function getQuery($name, $default = null)
	{
		return $this->vars['query'][$name] ?? $default;
	}

	public function setQuery($name, $value)
	{
		$this->vars['query'][$name] = $value;

		return $this;
	}

	public function __toString()
	{
		return (string) $this->toString();
	}

	public function routeTo($baseUri)
	{
		$uri = clone $this;
		$uri->setBaseUri($uri->getBaseUri() . '/' . $baseUri);

		return $uri->toString(false);
	}
}