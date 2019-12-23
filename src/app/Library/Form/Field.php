<?php

namespace MaiVu\Hummingbird\Lib\Form;

use MaiVu\Php\Filter;
use MaiVu\Hummingbird\Lib\Factory;
use MaiVu\Hummingbird\Lib\Form\Rule\Rule;
use MaiVu\Hummingbird\Lib\Helper\Asset;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Helper\Text as CmsText;
use MaiVu\Php\Registry;

abstract class Field
{
	/** @var Form */
	protected $form = null;

	/** @var string */
	protected $group = null;

	/** @var string */
	protected $type = '';

	/** @var string */
	protected $name = '';

	/** @var string */
	protected $renderName = null;

	/** @var string */
	protected $label = '';

	/** @var string */
	protected $description = '';

	/** @var string */
	protected $class = '';

	/** @var string */
	protected $id = '';

	/** @var boolean */
	protected $required = false;

	/** @var boolean */
	protected $readonly = false;

	/** @var array */
	protected $dataAttributes = [];

	/** @var array */
	protected $filters = [];

	/** @var array */
	protected $rules = [];

	/** @var array */
	protected $messages = [];

	/** @var array */
	protected $errorMessages = [];

	/** @var string */
	protected $showOn = '';

	/** @var mixed */
	protected $value = null;

	/** @var string | null */
	protected $confirmField = null;

	/** @var string | null */
	protected $regex = null;

	/** @var boolean */
	protected $translate = false;

	/** @var integer */
	protected $ucmFieldId = 0;

	/** @var string */
	protected $language = '*';

	/** @var array */
	protected $translationsData = [];

	abstract public function toString();

	public function __construct($config, Form $form = null)
	{
		if ($form)
		{
			$this->setForm($form);
		}

		$this->load($config);
	}

	public function load($config)
	{
		$config = (new Registry($config))->toArray();

		foreach ($config as $k => $v)
		{
			$this->set($k, $v);
		}

		return $this;
	}

	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	public function getForm()
	{
		return $this->form;
	}

	public function set($attribute, $value)
	{
		if (property_exists($this, $attribute))
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
		}

		return $this;
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

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	public function setType($type)
	{
		$this->type = ucfirst($type);

		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

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

	public function setTranslate($value)
	{
		$this->translate = (Language::isMultilingual() && $value);

		return $this;
	}

	public function setLanguage($language)
	{
		$defaultLanguage = Language::getDefault('site')->get('locale.code');
		$this->language  = Language::isMultilingual() && $defaultLanguage !== $language ? $language : '*';

		return $this;
	}

	public function getName($rawName = false)
	{
		if ($rawName)
		{
			return $this->name;
		}

		return $this->renderName ?: ($this->form ? $this->form->getRenderFieldName($this->name) : $this->name);
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function setRules(array $rules)
	{
		$this->rules = [];

		foreach ($rules as $rule)
		{
			$ruleClass = 'MaiVu\\Hummingbird\\Lib\\Form\\Rule\\' . $rule;

			if (class_exists($ruleClass))
			{
				$this->rules[$rule] = new $ruleClass;
			}
		}
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;

		return $this;
	}

	public function getFilters()
	{
		return $this->filters;
	}

	public function cleanValue($value)
	{
		$filters = $this->getFilters();

		if (!empty($value) && count($filters))
		{
			$value = Filter::clean($value, $filters);
		}

		return $value;
	}

	public function applyFilters($value = null)
	{
		if (null === $value)
		{
			$value = $this->getValue();
		}

		$value = $this->cleanValue($value);

		// Update value
		$this->setValue($value);

		return $value;
	}

	public function setShowOn($showOnData)
	{
		$this->showOn = str_replace(' & ', '&', trim($showOnData));
		$this->showOn = str_replace(' | ', '|', $this->showOn);
		$this->showOn = str_replace(' : ', ':', $this->showOn);

		return $this;
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

		if ($showOnData)
		{
			Asset::addFile('show-on.js');
		}

		return $showOnData;
	}

	public function isValid()
	{
		$value               = $this->getValue();
		$isValid             = true;
		$this->errorMessages = [];
		$placeHolders        = [
			'field' => CmsText::_($this->label ?: $this->name),
		];

		if ($this->required && ($value != '0' && empty($value)))
		{
			$isValid = false;

			if (isset($this->messages['requireMessage']))
			{
				$this->errorMessages[] = CmsText::_($this->messages['requireMessage'], $placeHolders);
			}
			else
			{
				$this->errorMessages[] = CmsText::_('required-field-msg', $placeHolders);
			}
		}

		if (count($this->rules))
		{
			/** @var Rule $ruleHandler */

			foreach ($this->rules as $ruleName => $ruleHandler)
			{
				if (!$ruleHandler->validate($this))
				{
					$isValid = false;

					if (isset($this->messages[$ruleName]))
					{
						$this->errorMessages[] = CmsText::_($this->messages[$ruleName], $placeHolders);
					}
					else
					{
						$this->errorMessages[] = CmsText::_('invalid-field-value-msg', $placeHolders);
					}
				}
			}
		}

		return $isValid;
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

	public function render()
	{
		return Factory::getService('view')
			->getPartial('Form/RenderField', ['field' => $this]);
	}

	public function getConfirmField()
	{
		if ($form = $this->getForm())
		{
			return $this->confirmField ? $form->getField($this->confirmField) : false;
		}

		return false;
	}

	public function getTranslationData($language = null)
	{
		if (null === $language)
		{
			return $this->translationsData;
		}

		return isset($this->translationsData[$language]) ? $this->translationsData[$language] : null;
	}

	public function setTranslationData($dataValue, $language = null)
	{
		if (null === $language && is_array($dataValue))
		{
			foreach ($dataValue as $langCode => $value)
			{
				$this->translationsData[$langCode] = $this->cleanValue($value);
			}
		}
		else
		{
			$this->translationsData[$language] = $this->cleanValue($dataValue);
		}

		return $this;
	}

	public function applyTranslationValue($language)
	{
		$this->setValue($this->getTranslationData($language));

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
}