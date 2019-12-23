<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use MaiVu\Php\Registry;
use Phalcon\Mvc\Model;
use MaiVu\Hummingbird\Lib\Helper\StringHelper;
use MaiVu\Hummingbird\Lib\Helper\Date;
use MaiVu\Hummingbird\Lib\Helper\User;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Form\Form;
use MaiVu\Hummingbird\Lib\Form\FormsManager;

class ModelBase extends Model
{
	/** @var string */
	protected $titleField = null;

	/** @var string */
	protected $standardMetadata = false;

	public function getTitleField()
	{
		return $this->titleField;
	}

	public function getParamsFormsManager()
	{
		return new FormsManager;
	}

	public function getIgnorePrefixModelName()
	{
		return str_replace('MaiVu\\Hummingbird\\Lib\\Mvc\\Model\\', '', get_class($this));
	}

	public function getOrderFields()
	{
		return [];
	}

	public function getSearchFields()
	{
		return $this->titleField ? [$this->titleField] : [];
	}

	public function getFilterForm()
	{
		return new Form('FilterForm', __DIR__ . '/Form/' . $this->getIgnorePrefixModelName() . '/Filter.php');
	}

	public function getFormsManager()
	{
		$formsManager = new FormsManager;
		$formsManager->set('general', new Form('FormData', __DIR__ . '/Form/' . $this->getIgnorePrefixModelName() . '/General.php'));

		return $formsManager;
	}

	public function checkin()
	{
		if ($this->id && $this->standardMetadata)
		{
			$this->assign(
				[
					'checkedAt' => Date::getInstance()->toSql(),
					'checkedBy' => User::getInstance()->id,
				]
			)->save();
		}

		return $this;
	}

	public function checkout()
	{
		if ($this->id && $this->standardMetadata)
		{
			$this->assign(
				[
					'checkedAt' => null,
					'checkedBy' => 0,
				]
			)->save();
		}

		return $this;
	}

	public function isCheckedIn()
	{
		if ($this->standardMetadata)
		{
			return $this->checkedAt && $this->checkedBy;
		}

		return false;
	}

	public function delete(): bool
	{
		if (!property_exists($this, 'id'))
		{
			return parent::delete();
		}

		if ($result = parent::delete())
		{
			$thisId = (int) $this->id;

			if ($this instanceof UcmItem)
			{
				$this->_modelsManager->executeQuery('DELETE FROM ' . UcmItemMap::class . ' WHERE itemId1 = ' . $thisId);
			}
			elseif ($this instanceof UcmComment)
			{
				$this->_modelsManager->executeQuery('DELETE FROM ' . UcmComment::class . ' WHERE parentId = ' . $thisId);
			}
		}

		return $result;
	}

	public function copy()
	{
		/** @var ModelBase $entity */
		$data = $this->toArray();
		unset($data['id']);
		$class  = get_class($this);
		$entity = new $class;

		if ($title = $this->getTitleField())
		{
			$data[$title] = StringHelper::increment($data[$title]);
		}

		if (isset($data['state']))
		{
			$data['state'] = 'U';
		}

		if (property_exists($this, 'createdAt'))
		{
			$data['createdAt'] = Date::getInstance()->toSql();
		}

		$entity->assign($data);

		return $entity->save() ? $entity : false;
	}

	public function beforeSave()
	{
		$paramsFormsManager = $this->getParamsFormsManager();

		if ($paramsFormsManager->count())
		{
			foreach ($paramsFormsManager->getForms() as $paramName => $paramForm)
			{
				if (property_exists($this, $paramName) && !is_string($this->{$paramName}))
				{
					if ($this->{$paramName} instanceof Registry)
					{
						$this->{$paramName} = $this->{$paramName}->toString();
					}
					else
					{
						$this->{$paramName} = json_encode($this->{$paramName});
					}
				}
			}
		}
	}

	public function beforeUpdate()
	{
		if ($this->hasStandardMetadata())
		{
			$this->assign(
				[
					'modifiedAt' => Date::getInstance()->toSql(),
					'modifiedBy' => User::getInstance()->id,
				]
			);
		}
	}

	public function hasStandardMetadata()
	{
		return $this->standardMetadata;
	}

	public function getRelatedItems()
	{
		$relatedItems = null;

		if ($this->tagContext && $this->tags->count())
		{
			$tagIds = [];

			foreach ($this->tags as $tag)
			{
				$tagIds[] = (int) $tag->id;
			}

			$relatedItems = $this->getModelsManager()
				->createBuilder()
				->from(['item' => get_class($this)])
				->innerJoin(UcmItemMap::class, 'ItemTagMap.itemId1 = item.id', 'ItemTagMap')
				->innerJoin(Tag::class, 'ItemTag.id = ItemTagMap.itemId2', 'ItemTag')
				->where('ItemTagMap.context = :context: AND ItemTagMap.itemId1 <> :notId: AND ItemTag.id IN ({tagIds:array})',
					[
						'context' => $this->tagContext,
						'notId'   => $this->id,
						'tagIds'  => $tagIds,
					]
				)
				->getQuery()
				->execute();
		}

		return $relatedItems;
	}

	public function getTranslations($language = null, $asArray = false)
	{
		$refKey = $this->id;

		if (null === $language)
		{
			$language = Language::getLanguageQuery();
		}

		if (empty($refKey) || '*' === $language)
		{
			return [];
		}

		static $translations = [];
		$refTable = preg_replace('/^' . preg_quote($this->_modelsManager->getModelPrefix(), '/') . '/', '', $this->getSource(), 1);
		$tranKey  = $refTable . ':' . $refKey . ':' . $language;

		if (!isset($translations[$tranKey . '_array']))
		{
			$entities = Translation::find(
				[
					'conditions' => 'translationId LIKE :translationId:',
					'bind'       => [
						'translationId' => $language . '.' . $refTable . '.id=' . $refKey . '.%',
					],
				]
			);

			if ($entities->count())
			{
				foreach ($entities as $entity)
				{
					list($langCode, $refTable, $refKey, $refField) = explode('.', $entity->translationId);
					$translations[$tranKey . '_value'][$refField] = $entity->translatedValue;
					$translations[$tranKey . '_array'][]          = [
						'language'        => $langCode,
						'refTable'        => $refTable,
						'refKey'          => $refKey,
						'refField'        => $refField,
						'originalValue'   => $entity->originalValue,
						'translatedValue' => $entity->translatedValue,
					];
				}
			}
			else
			{
				$translations[$tranKey . '_array'] = [];
				$translations[$tranKey . '_value'] = [];
			}
		}

		return $translations[$asArray ? $tranKey . '_array' : $tranKey . '_value'];
	}

	protected function afterDelete()
	{
		$prefix   = $this->getModelsManager()->getModelPrefix();
		$refTable = str_replace($prefix, '', $this->getIgnorePrefixModelName());
		$this->getDI()->get('db')->execute('DELETE FROM ' . $prefix . 'translations WHERE translationId LIKE :translationId',
			[
				'translationId' => '%.' . $refTable . '.id:' . $this->id . '.%',
			]
		);
	}

	public function t($field)
	{
		$translations = $this->getTranslations();

		if (isset($translations[$field]))
		{
			return $translations[$field];
		}

		return $this->{$field};
	}

	public function controllerBeforeBindData(&$rawData)
	{
		// Todo something
	}

	public function controllerDoBeforeSave(&$validData)
	{
		// Todo something
	}

	public function controllerDoAfterSave($validData)
	{
		// Todo something
	}
}
