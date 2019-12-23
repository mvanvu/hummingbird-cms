<?php

namespace MaiVu\Hummingbird\Lib\Form;

class FormsManager
{
	protected $forms = [];
	protected $messages = [];

	/**
	 * @param $formName
	 *
	 * @return Form
	 */

	public function get($formName)
	{
		return $this->forms[$formName];
	}

	public function set($formName, Form $form)
	{
		$this->forms[$formName] = $form;

		return $this;
	}

	public function has($formName)
	{
		return array_key_exists($formName, $this->forms);
	}

	public function getForms()
	{
		return $this->forms;
	}

	public function remove($formName)
	{
		unset($this->forms[$formName]);

		return $this;
	}

	public function count()
	{
		return count($this->forms);
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function bind($data)
	{
		$this->messages = [];
		$validData      = [];
		$isValid        = true;

		foreach ($this->forms as $form)
		{
			$validData = array_merge($validData, $form->bind($data));

			if (!$form->isValid())
			{
				$this->messages = array_merge($this->messages, $form->getMessages());
				$isValid        = false;
			}
		}

		return $isValid ? $validData : false;
	}
}