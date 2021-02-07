<?php

namespace App\Helper;

use App\Mvc\Model\Translation;
use App\Mvc\Model\UcmField as FieldModel;
use App\Mvc\Model\UcmField as UcmFieldModel;
use App\Mvc\Model\UcmFieldValue;
use App\Mvc\Model\UcmGroupField;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Form\FormsManager;
use Phalcon\Mvc\Model\Resultset\Simple;

class UcmField
{
	public static function getFieldsData($context, $id)
	{
		static $fieldsData = [];
		$k = $context . ':' . $id;

		if (!isset($fieldsData[$k]))
		{
			/** @var Simple $values */
			$fieldsData[$k] = [];
			$values         = Service::modelsManager()
				->createBuilder()
				->columns('name, fieldId, value')
				->from(['fieldValue' => UcmFieldValue::class])
				->innerJoin(UcmFieldModel::class, 'field.id = fieldValue.fieldId', 'field')
				->where('field.context = :context:', ['context' => $context])
				->andWhere('fieldValue.itemId = :thisId:', ['thisId' => $id])
				->getQuery()
				->execute();

			if ($values->count())
			{
				$fields = [];

				foreach ($values as $value)
				{
					$fieldsData[$k]['fields'][$value->name] = $value->value;
					$fields[$value->fieldId]                = $value->name;
				}

				if (Language::isMultilingual())
				{
					$language = Language::getLanguageQuery();
					$isSite   = Uri::isClient('site');

					if ($isSite && '*' === $language)
					{
						return $fieldsData[$k];
					}

					// Find translation fields values
					$query = Service::modelsManager()
						->createBuilder()
						->from(Translation::class)
						->columns('translationId, translatedValue')
						->where('translationId LIKE :translationId:',
							[
								'translationId' => ($isSite ? $language : '%') . '.ucm_field_values.fieldId=%,itemId=' . $id,
							]
						);

					$trans = $query->getQuery()->execute();

					if ($trans->count())
					{
						foreach ($trans as $tran)
						{
							list($langCode, $refTable, $refKey) = explode('.', $tran->translationId, 3);
							$refKey  = explode(',', $refKey, 2);
							$fieldId = str_replace('fieldId=', '', $refKey[0]);
							$value   = json_decode($tran->translatedValue, true) ?: [];

							if (isset($fields[$fieldId]) && !empty($value[0]))
							{
								$fieldsData[$k]['fields']['i18n'][$langCode][$fields[$fieldId]] = $value[0];
							}
						}
					}
				}
			}
		}

		return $fieldsData[$k];
	}

	public static function displayValue(string $value): string
	{
		if (strpos($value, '{') === 0 || strpos($value, '[') === 0)
		{
			$json = json_decode($value, true);

			if (json_last_error() === JSON_ERROR_NONE)
			{
				return implode(', ', $json);
			}
		}

		return $value;
	}

	public static function buildUcmFormsFields($fieldContext, $cid = 0)
	{
		$formsManager = new FormsManager;

		if (empty($fieldContext))
		{
			return $formsManager;
		}

		$groups = static::findGroups($fieldContext);

		if ($groups->count())
		{
			foreach ($groups as $group)
			{
				if ($fieldsData = static::parseFields($group->fields, $cid))
				{
					$formsManager->set($group->title, Form::create('fields', $fieldsData));
				}
			}
		}

		return $formsManager;
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
					'name'           => $field->name,
					'type'           => $field->type,
					'label'          => $field->label,
					'translate'      => $params->get('translate') === 'Y',
					'required'       => $params->get('required') === 'Y',
					'rules'          => $params->get('rules', []),
					'dataAttributes' => [
						'field-id' => $field->id,
					],
				];

				switch ($field->type)
				{
					case 'Check':
						$fieldData['checkboxValue'] = $params->get('checkboxValue', 'Y');
						$value                      = $params->get('checked') === 'Y' ? $fieldData['checkboxValue'] : null;
						break;

					case 'Switcher':
						$fieldData['checkboxValue'] = 'Y';
						$fieldData['filters']       = ['yesNo'];
						$value                      = $params->get('checked') === 'Y' ? 'Y' : 'N';
						break;

					case 'Select':
					case 'CheckList':
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
					case 'CmsTinyMCE':
					case 'CmsCodeMirror':
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

	public static function buildUcmFormFields($fieldContext, $cid = 0)
	{
		$form   = Form::create('fields');
		$fields = static::findFields($fieldContext);

		if ($fieldsData = static::parseFields($fields, $cid))
		{
			$form->load($fieldsData);
		}

		return $form;
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
}