<?php

namespace MaiVu\Hummingbird\Lib\Form;

use MaiVu\Php\Registry;

class Form
{
	/** @var string */
	protected $name;

	/** @var Registry */
	protected $data;

	/** @var array */
	protected $fields = [];

	/** @var array */
	protected $messages = [];

	/** @var string */
	protected $prefixNameField = '';

	/** @var string */
	protected $suffixNameField = '';

	public function __construct($name, $data = null, $rootKey = null)
	{
		$this->name = $name;

		if (strpos($name, '.'))
		{
			$parts  = explode('.', $name);
			$prefix = array_shift($parts) . '{prefix}';
			$count  = count($parts);

			if ($count > 1)
			{
				$prefix .= '[' . implode('][', $parts) . ']';
			}
			elseif ($count === 1)
			{
				$prefix .= '[' . $parts[0] . ']';
			}

			$this->prefixNameField = $prefix . '[';
			$this->suffixNameField = ']';
		}
		else
		{
			$this->prefixNameField = $name . '{prefix}[';
			$this->suffixNameField = ']';
		}

		$this->data = new Registry;

		if ($data)
		{
			$this->load($data, $rootKey);
		}
	}

	public function getRenderFieldName($fieldName, $language = null)
	{
		$replace = $language ? '[translations][' . $language . ']' : '';
		$subject = $this->prefixNameField . $fieldName . $this->suffixNameField;

		return str_replace('{prefix}', $replace, $subject);
	}

	public function setFieldsTranslationData(array $translationsData = [])
	{
		/** @var Field $field */
		foreach ($this->fields as $field)
		{
			$fieldName = $field->getName(true);

			if (isset($translationsData[$fieldName]))
			{
				$field->setTranslationData($translationsData[$fieldName]);
			}
		}
	}

	public function bind($data)
	{
		$registry     = new Registry($data);
		$filteredData = [];

		/** @var Field $field */
		foreach ($this->fields as $field)
		{
			$fieldName                = $field->getName(true);
			$filteredData[$fieldName] = $field->applyFilters($registry->get($fieldName));
		}

		$this->data->merge($filteredData);

		return $filteredData;
	}

	protected function loadField($config)
	{
		if (isset($config['type']))
		{
			$fieldClass = 'MaiVu\\Hummingbird\\Lib\\Form\\Field\\' . $config['type'];

			if (class_exists($fieldClass))
			{
				/** @var Field $field */
				$field               = new $fieldClass($config, $this);
				$name                = $field->getName(true);
				$this->fields[$name] = $field;
			}
		}
	}

	public function load($data, $rootKey = null)
	{
		$data = $this->data->parse($data);

		if ($rootKey && isset($data[$rootKey]))
		{
			$data = $data[$rootKey];
		}

		foreach ($data as $config)
		{
			$this->loadField($config);
		}

		return $this;
	}

	/**
	 * @param $name
	 *
	 * @return Field | false
	 */

	public function getField($name)
	{
		if (isset($this->fields[$name]))
		{
			return $this->fields[$name];
		}

		return false;
	}

	public function addField(Field $field)
	{
		$this->fields[$field->getName(true)] = $field;
		$field->setForm($this);

		return $this;
	}

	public function renderField($name)
	{
		/** @var Field $field */
		if ($field = $this->getField($name))
		{
			return $field->render();
		}

		return null;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getName()
	{
		return $this->name;
	}

	public function renderFields()
	{
		$results = [];

		/** @var Field $field */
		foreach ($this->fields as $field)
		{
			$results[] = $field->render();
		}

		return implode(PHP_EOL, $results);
	}

	public function has($fieldName)
	{
		return isset($this->fields[$fieldName]);
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

	public function isValid($bindData = null)
	{
		$this->messages = [];
		$isValid        = true;

		if (null !== $bindData)
		{
			$this->bind($bindData);
		}

		/** @var Field $field */
		foreach ($this->fields as $field)
		{
			if (!$field->isValid())
			{
				$isValid        = false;
				$this->messages = array_merge($this->messages, $field->get('errorMessages', []));
			}
		}

		return $isValid;
	}

	public function remove($fieldName)
	{
		if (isset($this->fields[$fieldName]))
		{
			unset($this->fields[$fieldName]);
		}

		return $this;
	}
}