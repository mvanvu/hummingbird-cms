<?php

namespace MaiVu\Php\Form;

use ArrayAccess;
use Closure;
use MaiVu\Php\Filter;
use MaiVu\Php\Registry;

abstract class Field implements ArrayAccess
{
	protected $form = null;

	protected $group = null;

	protected $type = '';

	protected $name = '';

	protected $renderName = null;

	protected $label = '';

	protected $description = '';

	protected $class = '';

	protected $id = '';

	protected $required = false;

	protected $readonly = false;

	protected $disabled = false;

	protected $dataAttributes = [];

	protected $filters = [];

	protected $rules = [];

	protected $messages = [];

	protected $errorMessages = [];

	protected $showOn = '';

	protected $value = null;

	protected $translate = null;

	protected $language = null;

	protected $input = '';

	protected $renderTemplate = null;

	protected $translateFields = null;

	protected $originConfigData = [];

	public function __construct($config, Form $form = null)
	{
		$this->load($config);

		if ($form)
		{
			$form->addField($this);
		}
	}

	public function load($config)
	{
		$this->originConfigData = array_merge(
			[
				'name'           => null,
				'label'          => null,
				'value'          => null,
				'required'       => false,
				'dataAttributes' => [],
				'messages'       => [],
				'rules'          => [],
			],
			Registry::parseData($config)
		);

		return $this->reset();
	}

	public function reset()
	{
		foreach ($this->originConfigData as $attribute => $value)
		{
			$this->set($attribute, $value);
		}

		return $this;
	}

	public function set($attribute, $value)
	{
		$excludes = [
			'originConfigData',
			'translateFields',
		];

		if (!in_array($attribute, $excludes) && property_exists($this, $attribute))
		{
			$method = 'set' . ucfirst($attribute);

			if (method_exists($this, $method))
			{
				$this->{$method}($value);
			}
			else
			{
				$this->{$attribute} = $value;
			}

			$this->originConfigData[$attribute] = $this->{$attribute};
		}

		return $this;
	}

	public static function create($config, Form $form = null): Field
	{
		return new static($config, $form);
	}

	public function setTranslate($value)
	{
		if (is_array($value))
		{
			$this->translate = [
				'required' => boolval($value['required'] ?? false),
				'rules'    => $value['rules'] ?? [],
				'messages' => $value['messages'] ?? [],
			];

			if (!empty($value['filters']))
			{
				$this->translate['filters'] = $value['filters'];
			}
		}
		elseif ($value)
		{
			$this->translate = [
				'required' => false,
				'rules'    => [],
				'messages' => [],
			];
		}

		return $this;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function setRules(array $rules)
	{
		$this->rules = [];

		foreach ($rules as $key => $value)
		{
			$this->setRule($key, $value);
		}

		return $this;
	}

	public function setRule($key, $value)
	{
		if ($value instanceof Closure || $value instanceof Rule)
		{
			$this->rules[$key] = $value;

			return $this;
		}

		if (is_integer($key))
		{
			$rule = $value;
		}
		else
		{
			$rule = $key;
			$this->setMessage($key, $value);
		}

		$ruleClass = null;
		$rawName   = $rule;
		$params    = [];

		if (false !== strpos($rawName, ':'))
		{
			list($rule, $params) = explode(':', $rawName, 2);
			$tmp = [];

			foreach (explode('|', $params) as $param)
			{
				if (false === strpos($param, '='))
				{
					$tmp[] = $param;
				}
				else
				{
					list($k, $v) = explode('=', $param, 2);
					$tmp[$k] = $v;
				}
			}

			$params = $tmp;
		}

		if (false === strpos($rule, '\\'))
		{
			$namespaces = array_merge([Rule::class], Form::getOptions()['ruleNamespaces']);

			foreach ($namespaces as $namespace)
			{
				if (class_exists($namespace . '\\' . $rule))
				{
					$ruleClass = $namespace . '\\' . $rule;
					break;
				}
			}
		}
		elseif (class_exists($rule))
		{
			$ruleClass = $rule;
		}

		if ($ruleClass)
		{
			$ruleObj = new $ruleClass($params);

			if ($ruleObj instanceof Rule)
			{
				$this->rules[$rawName] = $ruleObj;
			}
		}

		return $this;
	}

	public function setMessage($name, $value)
	{
		$this->messages[$name] = (string) $value;

		return $this;
	}

	public function setDataAttributes($value)
	{
		$this->dataAttributes = array_merge($this->dataAttributes, (array) $value);

		return $this;
	}

	public function applyFilters($value = null, $forceNull = false)
	{
		if (null === $value && !$forceNull)
		{
			// Default value
			$value = $this->getValue();
		}

		// Update value
		$this->setValue($this->cleanValue($value));

		// Always use $this->getValue() callback to get the value of this field
		return $this->getValue();
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	public function cleanValue($value)
	{
		if ($filters = $this->getFilters())
		{
			$value = Filter::clean($value, $filters);
		}

		return $value;
	}

	public function getFilters()
	{
		return $this->filters;
	}

	public function setFilters($filters)
	{
		$this->filters = (array) $filters;

		return $this;
	}

	public function isValid()
	{
		$value               = $this->getValue();
		$isValid             = true;
		$this->errorMessages = [];

		if ($this->required && ($value != '0' && empty($value)))
		{
			$isValid               = false;
			$this->errorMessages[] = $this->getRuleMessage('required');
		}

		if (count($this->rules))
		{
			/** @var Rule | Closure $ruleHandler */

			foreach ($this->rules as $ruleName => $ruleHandler)
			{
				if ($ruleHandler instanceof Closure)
				{
					$result = call_user_func_array($ruleHandler, [$this]);

					if (!is_bool($result))
					{
						$result = false;
					}
				}
				else
				{
					$result = $ruleHandler->validate($this);
				}

				if (!$result)
				{
					$isValid               = false;
					$this->errorMessages[] = $this->getRuleMessage($ruleName);
				}
			}
		}

		if (!$isValid && $this->errorMessages)
		{
			Registry::session()->set($this->getMessageName(), $this->errorMessages);
		}

		return $isValid;
	}

	protected function getRuleMessage($ruleName)
	{
		$default      = Form::getOptions()['messages'];
		$placeHolders = [
			'field' => $this->_($this->label ?: $this->name),
		];

		if (isset($this->messages[$ruleName]))
		{
			return $this->_($this->messages[$ruleName], $placeHolders);
		}

		return $this->_($default[$ruleName] ?? $default['invalid'], $placeHolders);
	}

	public function _(string $text, array $placeHolders = [])
	{
		if (($translator = Form::getFieldTranslator()) instanceof Closure)
		{
			return call_user_func_array($translator, [$text, $placeHolders]);
		}

		if ($placeHolders)
		{
			foreach ($placeHolders as $name => $value)
			{
				$text = str_replace('%' . $name . '%', $value, $text);
			}
		}

		return $text;
	}

	protected function getMessageName()
	{
		$name = 'phpFormFieldMessage';

		if ($this->form)
		{
			$name .= '.' . $this->form->getName();
		}

		if ($this->language)
		{
			$name .= '.' . $this->language;
		}

		return $name . '.' . $this->name;
	}

	public function render($options = [])
	{
		static $paths = [];
		$options       = Form::getOptions($options);
		$template      = $options['template'];
		$languages     = $options['languages'];
		$templatePaths = array_merge([__DIR__ . '/tmpl'], $options['templatePaths']);

		if (!isset($paths[$template]))
		{
			// Default template is Bootstrap v4
			$paths[$template] = __DIR__ . '/tmpl/bootstrap';

			foreach ($templatePaths as $path)
			{
				$path .= '/' . $template . '/renderField.php';

				if (is_file($path))
				{
					$paths[$template] = $path;
					break;
				}
			}
		}

		$this->renderTemplate = $paths[$template];
		$this->input          = $this->toString();
		$id                   = $this->getId();
		$errors               = $this->getErrorMessages();

		if ($translates = $this->getTranslateFields())
		{
			$tabTitles   = ['<li class="active"><a href="#' . $id . '-tab">' . $this->convertLanguageToFlag(array_keys($languages)[0]) . '</a></li>'];
			$tabContents = ['<li class="active" id="' . $id . '-tab">' . $this->input . '</li>'];
			array_shift($languages);

			foreach ($translates as $code2 => $translate)
			{
				$tabTitles[]   = '<li><a href="#' . $translate->getId() . '-tab">' . $this->convertLanguageToFlag($code2) . '</a></li>';
				$tabContents[] = '<li id="' . $translate->getId() . '-tab">' . $translate->toString() . '</li>';
				$errors        = array_merge($errors, $translate->getErrorMessages());
			}

			$this->input = '<div class="php-form-translation-field"><ul>' . implode($tabContents) . '</ul><ul>' . implode($tabTitles) . '</ul></div>';
		}


		return $this->loadTemplate(
			$this->renderTemplate,
			[
				'id'          => $id,
				'label'       => trim($this->getLabel()),
				'description' => trim($this->getDescription()),
				'errors'      => $errors,
				'horizontal'  => $options['layout'] === 'horizontal',
				'showOn'      => $this->getShowOn(),
				'class'       => 'field-' . $this->getType(),
				'required'    => $this->get('required'),
			]
		);
	}

	abstract public function toString();

	public function getId()
	{
		if (empty($this->id))
		{
			$this->setId($this->getName());
		}

		return $this->id;
	}

	public function setId($id)
	{
		$this->id = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $id);
		$this->id = trim($this->id, '-_');

		return $this->id;
	}

	public function getName($rawName = false)
	{
		if ($rawName)
		{
			return $this->name;
		}

		return $this->renderName ?: ($this->form ? $this->form->getRenderFieldName($this->name, $this->language) : $this->name);
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getErrorMessages($flash = true)
	{
		return $flash ? Registry::session()->getFlash($this->getMessageName(), $this->errorMessages) : $this->errorMessages;
	}

	public function getTranslateFields()
	{
		if (null === $this->translateFields)
		{
			$this->translateFields = [];
			$languages             = Form::getOptions()['languages'];

			if (($translate = $this->get('translate')) && count($languages) > 1)
			{
				array_shift($languages);

				foreach ($languages as $code2 => $language)
				{
					$configData = array_merge(
						$this->get('originConfigData'),
						$translate,
						[
							'value'          => null,
							'renderTemplate' => $this->renderTemplate,
							'language'       => $language,
						]
					);

					if (isset($configData['id']))
					{
						$configData['id'] = $language . '-' . $configData['id'];
					}

					unset($configData['translate']);
					$this->translateFields[$code2] = new static($configData, $this->form ? clone $this->form : null);
				}
			}
		}

		return $this->translateFields;
	}

	public function get($attribute, $defaultValue = null)
	{
		if (property_exists($this, $attribute))
		{
			$method = 'get' . ucfirst($attribute);

			if (method_exists($this, $method))
			{
				return $this->{$method}($attribute);
			}

			return $this->{$attribute};
		}

		return $defaultValue;
	}

	public function convertLanguageToFlag($language)
	{
		$langCode2 = substr($language, 0, 2);

		if (function_exists('mb_convert_encoding'))
		{
			$flag = '';

			foreach (str_split(strtoupper($langCode2)) as $char)
			{
				$stringRegional = ord($char) + 127397;
				$flag           .= mb_convert_encoding('&#' . $stringRegional . ';', 'UTF-8', 'HTML-ENTITIES');
			}

			return $flag;
		}

		return $language;
	}

	protected function loadTemplate($path, array $displayData = [])
	{
		ob_start();
		include $path;

		return ob_get_clean();
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getShowOn()
	{
		$showOnData = [];

		if (empty($this->showOn))
		{
			return $showOnData;
		}

		$formName    = $this->form->getName();
		$showOnParts = preg_split('/(\||\&)/', $this->showOn, -1, PREG_SPLIT_DELIM_CAPTURE);
		$op          = '';

		foreach ($showOnParts as $showOnPart)
		{
			if ('|' === $showOnPart || '&' === $showOnPart)
			{
				$op = $showOnPart;
				continue;
			}

			list ($fieldName, $value) = explode(':', $showOnPart, 2);

			if ($this->form)
			{
				if (false === strpos($fieldName, '.'))
				{
					$fieldName = $this->form->getRenderFieldName($fieldName);
				}
				else
				{
					$parts       = explode('.', $fieldName);
					$fieldName   = array_pop($parts);
					$tmpFormName = implode('.', $parts);

					if ($tmpFormName === $formName)
					{
						$fieldName = $this->form->getRenderFieldName($fieldName);
					}
					else
					{
						$tmpForm   = new Form($tmpFormName);
						$fieldName = $tmpForm->getRenderFieldName($fieldName);
						unset($tmpForm);
					}
				}
			}

			$showOnData[] = [
				'op'    => $op,
				'field' => $fieldName,
				'value' => $value,
			];

			if ('' !== $op)
			{
				$op = '';
			}
		}

		return $showOnData;
	}

	public function setShowOn($showOnData)
	{
		$this->showOn = preg_replace('/\s*(\&|\||:)\s*/', '${1}', trim($showOnData));

		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = ucfirst($type);

		return $this;
	}

	public function getForm(): ?Form
	{
		return $this->form;
	}

	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	public function __toString()
	{
		return $this->toString();
	}

	public function offsetExists($offset)
	{
		return property_exists($this, $offset);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value)
	{
		return $this->set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->set($offset, null);
	}

	protected function getDataAttributesString()
	{
		$dataAttributes = '';

		if ($this->dataAttributes)
		{
			foreach ($this->dataAttributes as $dataKey => $dataValue)
			{
				if (is_array($dataValue) || is_object($dataValue))
				{
					$dataValue = json_encode($dataValue);
				}

				$dataAttributes .= ' data-' . $dataKey . '="' . htmlspecialchars((string) $dataValue, ENT_COMPAT, 'UTF-8') . '"';
			}
		}

		return $dataAttributes;
	}

	protected function renderValue($value)
	{
		return htmlspecialchars((string) $value, ENT_COMPAT, 'UTF-8');
	}

	protected function renderText($text)
	{
		return htmlentities($this->_((string) $text));
	}
}