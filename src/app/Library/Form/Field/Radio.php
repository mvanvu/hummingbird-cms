<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

class Radio extends OptionAbstract
{
	public function toString()
	{
		$id        = $this->getId();
		$name      = $this->getName();
		$value     = $this->getValue();
		$data      = $this->getDataAttributesString();
		$i         = 0;
		$radioList = '';

		foreach ($this->getOptions() as $k => $v)
		{
			$radioId   = $id . $i++;
			$radioList .= '<label class="uk-display-block" for="' . $radioId . '">'
				. '<input class="' . rtrim('uk-radio ' . $this->class) . '" type="radio" name="' . $name . '" id="' . $radioId . '"'
				. ' value="' . htmlspecialchars($k, ENT_COMPAT, 'UTF-8') . '"' . $data;

			if ($this->required)
			{
				$radioList .= ' required';
			}

			if ($this->readonly)
			{
				$radioList .= ' readonly';
			}

			if ($k == $value)
			{
				$radioList .= ' checked';
			}

			$radioList .= '/>&nbsp;' . $v . ' </label>';
		}

		return $radioList;
	}
}