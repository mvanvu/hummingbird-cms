<?php

namespace App\Helper;

use App\Mvc\Model\UcmItem as Item;
use MaiVu\Php\Registry;
use Phalcon\Mvc\View\Engine\Volt\Compiler;

class Volt
{
	/** @var Compiler */
	protected static $compiler;

	public function __construct(Compiler $compiler)
	{
		static::$compiler = $compiler;
	}

	public static function getCompiler()
	{
		return static::$compiler;
	}

	public static function voidFilter($arguments)
	{

	}

	public static function publicResource($baseFile, bool $image = false)
	{
		$baseFile = trim($baseFile, '/\\\\.');

		if (Uri::isClient('site'))
		{
			$publicResource = TPL_SITE_PATH . '/public/' . $baseFile;

			if (is_file($publicResource))
			{
				return ROOT_URI . '/hb/io/public/' . Template::getTemplate()->id . '/' . $baseFile;
			}
		}

		return ROOT_URI . '/' . $baseFile;
	}

	public function compileFunction($name, $resolvedArgs, $exprArgs)
	{
		$helperPrefix = Constant::NAMESPACE_HELPER . '\\';

		switch ($name)
		{
			case '_':
				return $helperPrefix . 'Text::_(' . $resolvedArgs . ')';

			case '_s':
				return $helperPrefix . 'Text::plural(' . $resolvedArgs . ')';

			case 'addAssets':
				return $helperPrefix . 'Assets::add(' . $resolvedArgs . ')';

			case 'currency':
			case 'price':
				return $helperPrefix . 'Currency::format(' . $resolvedArgs . ')';

			case 'widget':
				return $helperPrefix . 'Widget::renderPosition(' . $resolvedArgs . ')';

			case 'route':
				return $helperPrefix . 'Uri::route(' . $resolvedArgs . ')';

			case 'currentLink':
				return $helperPrefix . 'Uri::getActive()->toString()';

			case 'currentUri':
				return $helperPrefix . 'Uri::getActive()->toPath()';

			case 'rootUri':
				return 'constant(\'ROOT_URI\')';

			case 'baseUri':
				return $helperPrefix . 'Uri::getBaseUriPrefix()';

			case 'isHome':
				return $helperPrefix . 'Uri::isHome()';

			case 'isAdmin':
				return $helperPrefix . 'Uri::isClient(\'administrator\')';

			case 'isSite':
				return $helperPrefix . 'Uri::isClient(\'site\')';

			case 'menu':
				return $helperPrefix . 'Menu::renderMenu(' . $resolvedArgs . ')';

			case 'trigger':
				return $helperPrefix . 'Event::trigger(' . $resolvedArgs . ')';

			case 'user':
				return $helperPrefix . 'User::getInstance(' . $resolvedArgs . ')';

			case 'isEmpty':
				return 'empty(' . $resolvedArgs . ')';

			case 'isSet':
				return 'isset(' . $resolvedArgs . ')';

			case 'icon':
				return $helperPrefix . 'IconSvg::render(' . $resolvedArgs . ')';

			case 'csrfInput':
				return $helperPrefix . 'Form::tokenInput(' . $resolvedArgs . ')';

			case 'csrf':
				return $helperPrefix . 'Form::getToken(' . $resolvedArgs . ')';

			case 'public':
				return $helperPrefix . 'Volt::publicResource(' . $resolvedArgs . ')';

			case 'reCaptcha':
				return $helperPrefix . 'ReCaptcha::render()';

			case 'language':
				return $helperPrefix . 'Language::getActiveLanguage()';

			case 'isUri':
				return $helperPrefix . 'Uri::is(' . $resolvedArgs . ')';

			case 'ucmItem':
				return Item::class . '::findFirst(' . $resolvedArgs . ')';

			case 'helper':
				$helperMethod = str_replace('\'', '', static::$compiler->expression($exprArgs[0]['expr']));
				$resolvedArgs = [];

				for ($i = 1, $n = count($exprArgs); $i < $n; $i++)
				{
					$resolvedArgs[] = static::$compiler->expression($exprArgs[$i]['expr']);
				}

				$resolvedArgs = implode(',', $resolvedArgs);

				return $helperPrefix . $helperMethod . '(' . $resolvedArgs . ')';

			case 'registry':
				return Registry::class . '::create(' . $resolvedArgs . ')';

			default:

				if (function_exists($name))
				{
					return $name . '(' . $resolvedArgs . ')';
				}

		}
	}

	public function compileFilter($name, $resolvedArgs, $exprArgs)
	{
		switch ($name)
		{
			case 'j2nl':
				return 'implode(PHP_EOL, ' . $resolvedArgs . ')';

			case 'void':
				return Volt::class . '::voidFilter(' . $resolvedArgs . ')';

			default:

				if (function_exists($name))
				{
					return $name . '(' . $resolvedArgs . ')';
				}
		}
	}
}