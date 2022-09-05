<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Form;

class SubForm extends Field
{
	protected $horizontal = false;
	protected $columns = 1;
	protected $fields = [];
	protected $subForm = null;

	public function toString()
	{
		$form    = $this->getSubForm();
		$columns = (int) $this->columns;

		if (!in_array($columns, [1, 2, 3, 4, 6]))
		{
			$columns = 1;
		}

		if (!empty($this->value))
		{
			$form->bind($this->value);
		}

		return '<div class="' . rtrim('subform-field-body ' . $this->class) . '" '
			. 'data-input-id="' . $this->getId() . '"' . $this->getDataAttributesString() . '>'
			. $this->loadTemplate(
				dirname($this->renderTemplate) . '/fields/subform.php',
				[
					'form'    => $form,
					'columns' => $columns,
					'options' => $this->horizontal ? ['layout' => 'horizontal'] : [],
				]
			) . '</div>';
	}

	public function getSubForm()
	{
		if (null === $this->subForm)
		{
			$formName      = trim(($this->form ? $this->form->getName() . '.' : '') . $this->name, '.');
			$this->subForm = Form::create($formName, $this->fields);
		}

		return $this->subForm;
	}

	public function isValid()
	{
		return $this->getSubForm()->isValid($this->value);
	}
}