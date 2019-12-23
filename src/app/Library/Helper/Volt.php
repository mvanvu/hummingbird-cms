<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use Phalcon\Mvc\View\Engine\Volt\Compiler;
use MaiVu\Hummingbird\Lib\Form\Field;

class Volt
{
	/** @var Compiler */
	protected static $compiler;

	public function __construct(Compiler $compiler)
	{
		self::$compiler = $compiler;
	}

	public static function getCompiler()
	{
		return self::$compiler;
	}

	public function compileFunction($name, $resolvedArgs, $exprArgs)
	{
		$helperPrefix = 'MaiVu\\Hummingbird\\Lib\\Helper\\';

		switch ($name)
		{
			case '_':

				return $helperPrefix . 'Text::_(' . $resolvedArgs . ')';

			case 'widget':
				return $helperPrefix . 'Widget::renderPosition(' . $resolvedArgs . ')';

			case 'route':

				return $helperPrefix . 'Uri::route(' . $resolvedArgs . ')';

			case 'home':

				return $helperPrefix . 'Uri::isHome()';

			case 'admin':

				return $helperPrefix . 'Uri::isClient(\'administrator\')';

			case 'site':

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

			case 'helper':
				$helperMethod = str_replace('\'', '', self::$compiler->expression($exprArgs[0]['expr']));
				$resolvedArgs = [];

				for ($i = 1, $n = count($exprArgs); $i < $n; $i++)
				{
					$resolvedArgs[] = self::$compiler->expression($exprArgs[$i]['expr']);
				}

				$resolvedArgs = implode(',', $resolvedArgs);

				return $helperPrefix . $helperMethod . '(' . $resolvedArgs . ')';
		}

		if (function_exists($name))
		{
			return $name . '(' . $resolvedArgs . ')';
		}
	}

	public static function voidFilter($arguments)
	{
		return;
	}

	public function compileFilter($name, $resolvedArgs, $exprArgs)
	{
		switch ($name)
		{
			case 'j2nl':
				return 'implode(PHP_EOL, ' . $resolvedArgs . ')';

			case 'void':
				return 'MaiVu\\Hummingbird\\Lib\\Helper\\Volt::voidFilter(' . $resolvedArgs . ')';
		}

		if (function_exists($name))
		{
			return $name . '(' . $resolvedArgs . ')';
		}
	}

	public static function toLanguageField(Field $field, $language)
	{
		$defaultLanguage = Language::getDefault('site');
		$name            = $field->getName(true);

		if ($defaultLanguage->get('locale.code') !== $language)
		{
			$newField = clone $field;
			$id       = $field->getId();
			$newField->setLanguage($language);
			$newField->setId($id . '-' . $language);
			$newField->set('required', false);
			$newField->set('rules', []);
			$newField->applyTranslationValue($language);

			if ($form = $field->getForm())
			{
				$renderName = $form->getRenderFieldName($name, $language);
			}
			else
			{
				$renderName = 'FormData[translations][' . $language . ']';
			}

			$newField->set('renderName', $renderName);

			if (in_array($newField->getType(), ['Text', 'TextArea']))
			{
				$newField->set('hint', $field->getValue());
			}

			return $newField;
		}

		return $field;
	}
}