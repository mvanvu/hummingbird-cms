<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Form\Field;
use MaiVu\Hummingbird\Lib\Helper\Asset;

class TextArea extends Field
{
	/** @var int */
	protected $rows = 5;

	/** @var int */
	protected $cols = 15;

	/** @var string */
	protected $hint = '';

	/** @var string */
	protected $autocomplete = '';

	/** @var boolean */
	protected $useEmoji = false;

	public function toString()
	{
		$class = trim($this->class . ' uk-textarea');

		if ($this->useEmoji)
		{
			Asset::addFile('emoji.js');
			$class .= ' input-emoji';
		}

		$input = '<textarea class="' . $class . '"'
			. ' name="' . $this->getName() . '" id="' . $this->getId() . '"'
			. ' rows="' . (int) $this->rows . '" cols="' . (int) $this->cols . '"'
			. $this->getDataAttributesString();

		if ($this->required)
		{
			$input .= ' required';
		}

		if ($this->readonly)
		{
			$input .= ' readonly';
		}

		if ($this->hint)
		{
			$input .= ' placeholder="' . htmlspecialchars($this->hint, ENT_COMPAT, 'UTF-8') . '"';
		}

		if ($this->autocomplete)
		{
			$input .= ' autocomplete="' . htmlspecialchars($this->autocomplete, ENT_COMPAT, 'UTF-8') . '"';
		}

		$input .= '>' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';

		return $input;
	}
}