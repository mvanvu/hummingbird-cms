<?php

namespace MaiVu\Php\Form\Field\Base;

abstract class CheckBase extends OptionsBase
{
	protected $checkType = 'checkbox';

	protected $inline = false;

	public function toString()
	{
		$id    = $this->getId();
		$name  = $this->getName();
		$value = $this->getValue();
		$html  = '<div class="' . $this->checkType . '-list-field-container"' . $this->getDataAttributesString() . ' id="' . $id . '">';
		$i     = 0;
		settype($value, 'array');
		$template = dirname($this->renderTemplate) . '/fields/field-check.php';

		if ($this->get('multiple', false))
		{
			$name .= '[]';
		}

		foreach ($this->getOptions() as $option)
		{
			$inputValue = $this->renderValue($option['value'] ?? '');
			$html       .= $this->loadTemplate(
				$template,
				[
					'labelClass' => $option['labelClass'] ?? '',
					'class'      => $option['class'] ?? '',
					'id'         => $id . $i++,
					'name'       => $name,
					'type'       => $this->checkType,
					'label'      => $this->_($option['text'] ?? ''),
					'value'      => $inputValue,
					'required'   => $this->required,
					'inline'     => $this->inline,
					'readonly'   => boolval($option['readonly'] ?? false),
					'disabled'   => boolval($option['disabled'] ?? false),
					'checked'    => in_array($inputValue, $value),
				]
			);
		}

		return $html . '</div>';
	}
}