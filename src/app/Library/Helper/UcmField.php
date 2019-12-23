<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use MaiVu\Hummingbird\Lib\Mvc\Model\UcmGroupField;
use MaiVu\Hummingbird\Lib\Mvc\Model\UcmField as FieldModel;
use MaiVu\Hummingbird\Lib\Form\FormsManager;
use MaiVu\Hummingbird\Lib\Form\Form;

class UcmField
{
	protected static function parseFields($fields, $cid)
	{
		$fieldsData = [];

		if ($fields->count())
		{
			foreach ($fields as $field)
			{
				if ($cid && $field->categories->count())
				{
					$continue = true;

					foreach ($field->categories as $category)
					{
						if ($category->id == $cid)
						{
							$continue = false;
							break;
						}
					}

					if ($continue)
					{
						continue;
					}
				}

				$params    = $field->getParams();
				$value     = $params->get('value');
				$fieldData = [
					'ucmFieldId' => (int) $field->id,
					'name'       => $field->name,
					'type'       => $field->type,
					'label'      => $field->label,
					'translate'  => $params->get('translate') === 'Y',
					'required'   => $params->get('required') === 'Y',
					'rules'      => $params->get('rules', []),
				];

				switch ($field->type)
				{
					case 'Check':
						$fieldData['checkboxValue'] = $params->get('checkboxValue', 'Y');
						$value                      = $params->get('checked') === 'Y' ? $fieldData['checkboxValue'] : null;
						break;

					case 'Select':
					case 'Radio':
						$fieldData['options'] = $params->get('options', []);
						$fieldData['rules'][] = 'Options';

						if ('Select' === $field->type)
						{
							$fieldData['multiple'] = $params->get('multiple') === 'Y';

							if ($fieldData['multiple'] && $value)
							{
								$value = explode(',', (string) $value);
							}
						}

						break;

					case 'Email':
						$fieldData['rules'][] = 'Email';
						break;

					case 'TextArea':
						$fieldData['cols'] = $params->get('cols', 15, 'uint');
						$fieldData['rows'] = $params->get('rows', 3, 'uint');
						break;

					case 'CmsEditor':
					case 'CmsEditorCode':
						$fieldData['filters'] = ['html'];
						break;

					default:
						$fieldData['filters'] = ['string', 'trim'];
						break;

				}

				if ($hint = $params->get('hint'))
				{
					$fieldData['hint'] = $hint;
				}

				$fieldData['rules'] = array_unique($fieldData['rules']);
				$fieldData['value'] = $value;
				$fieldsData[]       = $fieldData;
			}
		}

		return $fieldsData;
	}

	public static function findGroups($fieldContext)
	{
		static $groups = [];

		if (!array_key_exists($fieldContext, $groups))
		{
			$groups[$fieldContext] = UcmGroupField::find(
				[
					'conditions' => 'context = :context:',
					'bind'       => [
						'context' => $fieldContext,
					],
				]
			);
		}

		return $groups[$fieldContext];
	}

	public static function findFields($fieldContext)
	{
		static $fields = [];

		if (!array_key_exists($fieldContext, $fields))
		{
			$fields[$fieldContext] = FieldModel::find(
				[
					'conditions' => 'state = :published: AND groupId = 0 AND context = :context:',
					'bind'       => [
						'published' => 'P',
						'context'   => $fieldContext,
					],
				]
			);
		}

		return $fields[$fieldContext];
	}

	public static function buildUcmFormsFields($fieldContext, $cid = 0)
	{
		$formsManager = new FormsManager;

		if (empty($fieldContext))
		{
			return $formsManager;
		}

		$groups = self::findGroups($fieldContext);

		if ($groups->count())
		{
			foreach ($groups as $group)
			{
				if ($fieldsData = self::parseFields($group->fields, $cid))
				{
					$formsManager->set($group->title, new Form('FormData.fields', $fieldsData));
				}
			}
		}

		return $formsManager;
	}

	public static function buildUcmFormFields($fieldContext, $cid = 0)
	{
		$form   = new Form('FormData.fields');
		$fields = self::findFields($fieldContext);

		if ($fieldsData = self::parseFields($fields, $cid))
		{
			$form->load($fieldsData);
		}

		return $form;
	}
}