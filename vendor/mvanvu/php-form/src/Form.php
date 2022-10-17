<?php

namespace MaiVu\Php\Form;

use ArrayAccess;
use Closure;
use MaiVu\Php\Registry;

class Form implements ArrayAccess
{
	protected static $fieldTranslator = null;
	protected static $options = [
		'fieldNamespaces' => [],
		'ruleNamespaces'  => [],
		'templatePaths'   => [],
		'template'        => 'bootstrap',
		'layout'          => 'vertical',
		'messages'        => [
			'required' => '%field% is required!',
			'invalid'  => '%field% is invalid!',
		],
		'languages'       => [
			// ISO code 2 => name
			// 'en' => 'en-GB',
			// 'vn' => 'vi-VN',
		],
	];

	protected $name;

	protected $data;

	protected $i18n;

	protected $fields = [];

	protected $messages = [];

	protected $prefixNameField = '';

	protected $suffixNameField = '';

	protected $beforeValidation = null;

	protected $afterValidation = null;

	public function __construct($name, $data = null)
	{
		if (
			is_array($name)
			|| is_object($name)
			|| preg_match('/\.(php|json|ini)$/', $name)
		) {
			$data = $name;
			$name = '';
		}

		$this->setName($name);
		$this->data = Registry::create();
		$this->i18n = Registry::create();

		if ($data) {
			$this->load($data);
		}
	}

	public function load($data)
	{
		foreach (Registry::parseData($data) as $config) {
			$this->loadField($config);
		}

		return $this;
	}

	protected function loadField($config)
	{
		if (isset($config['type'])) {
			$fieldClass = null;

			if (false === strpos($config['type'], '\\')) {
				$namespaces = array_merge([Field::class], static::$options['fieldNamespaces']);

				foreach ($namespaces as $namespace) {
					if (class_exists($namespace . '\\' . $config['type'])) {
						$fieldClass = $namespace . '\\' . $config['type'];
						break;
					}
				}
			} elseif (class_exists($config['type'])) {
				$fieldClass = $config['type'];
			}

			if ($fieldClass) {
				new $fieldClass($config, $this);
			}
		}
	}

	public static function create($name, $data = null)
	{
		return new Form($name, $data);
	}

	public static function getFieldTranslator()
	{
		return static::$fieldTranslator;
	}

	public static function setFieldTranslator(Closure $closure)
	{
		static::$fieldTranslator = $closure;
	}

	public static function addFieldNamespaces($namespaces)
	{
		static::$options['fieldNamespaces'] = array_merge(static::$options['fieldNamespaces'], (array) $namespaces);
	}

	public static function addRuleNamespaces($namespaces)
	{
		static::$options['ruleNamespaces'] = array_merge(static::$options['ruleNamespaces'], (array) $namespaces);
	}

	public static function addTemplatePaths($paths)
	{
		static::$options['templatePaths'] = array_merge(static::$options['templatePaths'], (array) $paths);
	}

	public static function setTemplate($template, $layout = 'vertical')
	{
		static::$options['template'] = $template;
		static::$options['layout']   = $layout;
	}

	public static function clearSessionMessages()
	{
		Registry::session()->remove('phpFormFieldMessage');
	}

	public static function getOptions(array $extendsOptions = [])
	{
		if ($extendsOptions) {
			return static::extendsOptions($extendsOptions);
		}

		return static::$options;
	}

	public static function setOptions(array $options)
	{
		static::$options = static::extendsOptions($options);
	}

	public static function extendsOptions(array $options)
	{
		$result = static::$options;

		foreach ($options as $name => $value) {
			if (isset($result[$name]) && gettype($value) === gettype($result[$name])) {
				if (is_array($value)) {
					$result[$name] = array_merge($result[$name], $value);
				} else {
					$result[$name] = $value;
				}
			}
		}

		return $result;
	}

	public static function setOption(string $key, $value)
	{
		if (array_key_exists($key, static::$options)) {
			static::$options[$key] = $value;
		}
	}

	public function reset()
	{
		foreach ($this->fields as $field) {
			$field->reset();
		}

		return $this;
	}

	public function beforeValidation($callback)
	{
		$this->beforeValidation = is_callable($callback) ? $callback : null;
	}

	public function afterValidation($callback)
	{
		$this->afterValidation = is_callable($callback) ? $callback : null;
	}

	public function getRenderFieldName($fieldName, $language = null)
	{
		$i18n      = $this->name ? '[i18n]' : 'i18n';
		$replace   = $language ? $i18n . '[' . $language . ']' : '';
		$subject   = $this->prefixNameField . $fieldName . $this->suffixNameField;
		$fieldName = str_replace('{replace}', $replace, $subject);

		if (!$language && 0 === strpos($fieldName, '[')) {
			$fieldName = trim($fieldName, '[]');
		}

		return $fieldName;
	}

	public function addField(Field $field)
	{
		$this->fields[$field->getName(true)] = $field;
		$field->setForm($this);

		return $this;
	}

	public function getData($toArray = false)
	{
		return $toArray ? $this->data->toArray() : $this->data;
	}

	public function renderHorizontal()
	{
		return $this->renderFields(['layout' => 'horizontal']);
	}

	public function renderFields(array $options = []): string
	{
		$results = [];

		foreach ($this->fields as $field) {
			$results[] = $this->renderField($field, $options);
		}

		return implode(PHP_EOL, $results);
	}

	public function renderField($field, $options = [])
	{
		if (!$field instanceof Field) {
			$field = $this->getField($field);
		}

		if ($field) {
			return $field->render($options);
		}

		return null;
	}

	/**
	 * @param $name
	 *
	 * @return Field | false
	 */

	public function getField($name)
	{
		if (isset($this->fields[$name])) {
			return $this->fields[$name];
		}

		return false;
	}

	public function renderTemplate(string $template, bool $horizontal = false)
	{
		$options = ['template' => $template];

		if ($horizontal) {
			$options['layout'] = 'horizontal';
		}

		return $this->renderFields($options);
	}

	public function count()
	{
		return count($this->fields);
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function isValidRequest()
	{
		return $this->isValid($_REQUEST);
	}

	public function isValid($bindData = null)
	{
		$this->messages = [];
		$isValid        = true;

		if (null !== $bindData) {
			$this->bind($bindData);
		}

		if ($this->beforeValidation) {
			call_user_func_array($this->beforeValidation, [$this]);
		}

		$this->validateFields($this->fields, $isValid);

		if ($this->afterValidation) {
			call_user_func_array($this->afterValidation, [$this, $isValid]);
		}

		return $isValid;
	}

	public function bind($data): Registry
	{
		$data        = Registry::create($data);
		$filtered    = Registry::create();
		$i18n        = Registry::create();
		$i18nPathGet = '';
		$i18nPathSet = '';

		if ($this->name) {
			if (strpos($this->name, '.')) {
				list($rootPath, $i18nPathGet) = explode('.', $this->name, 2);
				$i18n->setData($data->get($rootPath . '.i18n', []));
				$i18nPathGet .= '.';
			} else {
				$i18n->setData($data->get($this->name . '.i18n', []));
			}

			$data->setData($data->get($this->name, []));
			$i18nPathSet = $this->name . '.';
		} else {
			$i18n->setData($data->get('i18n', []));
		}

		foreach ($this->fields as $field) {
			$fieldName = $field->getName(true);

			if ($data->has($fieldName)) {
				$filtered->set($fieldName, $field->applyFilters($data->get($fieldName)));
			} elseif (!$this->data->has($fieldName)) {
				$this->data->set($fieldName, $field->applyFilters());
			}

			if ($translateFields = $field->getTranslateFields()) {
				$fieldValue = $field->getValue();

				foreach ($translateFields as $translateField) {
					$language = $translateField->get('language');
					$pathGet  = $language . '.' . $i18nPathGet . $fieldName;
					$pathSet  = $language . '.' . $i18nPathSet . $fieldName;

					if ($i18n->has($pathGet)) {
						$translatedValue = $translateField->applyFilters($i18n->get($pathGet));

						if ('' !== $translatedValue && $fieldValue != $translatedValue) {
							$this->i18n->set($pathSet, $translatedValue);
						}
					}
				}
			}
		}

		$this->data->merge($filtered);

		return $filtered;
	}

	protected function validateFields($fields, &$isValid)
	{
		/** @var Field $field */
		foreach ($fields as $field) {
			if (!$field->isValid()) {
				$isValid        = false;
				$this->messages = array_merge($this->messages, $field->getErrorMessages(false));
			}

			if ($translateFields = $field->getTranslateFields()) {
				$this->validateFields($translateFields, $isValid);
			}
		}
	}

	public function getI18nData($asArray = false)
	{
		return $asArray ? $this->i18n->toArray() : $this->i18n;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName(string $name)
	{
		$name = trim($name, '.');

		if (strpos($name, '.')) {
			$parts  = explode('.', $name);
			$prefix = array_shift($parts) . '{replace}';
			$count  = count($parts);

			if ($count > 1) {
				$prefix .= '[' . implode('][', $parts) . ']';
			} elseif ($count === 1) {
				$prefix .= '[' . $parts[0] . ']';
			}

			$this->prefixNameField = $prefix . '[';
			$this->suffixNameField = ']';
		} else {
			$this->prefixNameField = $name . '{replace}[';
			$this->suffixNameField = ']';
		}

		$this->name = $name;
	}

	public function offsetExists($offset): bool
	{
		return $this->has($offset);
	}

	public function has($fieldName): bool
	{
		return isset($this->fields[$fieldName]);
	}

	public function offsetUnset($offset): void
	{
		$this->remove($offset);
	}

	public function remove($fieldName)
	{
		if (isset($this->fields[$fieldName])) {
			unset($this->fields[$fieldName]);
		}

		return $this;
	}

	public function __get($name)
	{
		return $this->offsetGet($name);
	}

	public function __set($name, $value)
	{
		$this->offsetSet($name, $value);

		return $this;
	}

	public function offsetGet($offset): mixed
	{
		return $this->getField($offset);
	}

	public function offsetSet($offset, $value): void
	{
		if ($value instanceof Field) {
			$this->fields[$offset] = $value;
		}
	}

	public function __toString()
	{
		return $this->renderFields();
	}
}
