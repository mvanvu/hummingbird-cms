<?php

namespace App\Mvc\Model;

use App\Helper\Date;
use App\Helper\FileSystem;
use App\Helper\Language;
use App\Helper\Service;
use App\Helper\StringHelper;
use App\Helper\Text;
use App\Helper\User as Auth;
use App\Traits\Hooker;
use Exception;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Form\FormsManager;
use MaiVu\Php\Registry;
use Phalcon\Mvc\ModelInterface;

class ModelBase extends ModelPermission
{
	/**
	 * @var boolean
	 */
	protected static $instFound = false;

	/**
	 * @var string
	 */
	protected $titleField = null;

	/**
	 * @var array
	 */
	protected $jsonFields = [];

	/**
	 * @var string
	 */
	protected $standardMetadata = false;

	/**
	 * @param null        $identity
	 * @param string|null $modelClass
	 *
	 * @return ModelInterface
	 * @throws Exception
	 */

	use Hooker;

	public static function getInstanceOrFail($identity = null, string $modelClass = null)
	{
		$instance = static::getInstance($identity, $modelClass);

		if (static::$instFound)
		{
			return $instance;
		}

		throw new Exception(Text::_('404-message'), 404);
	}

	/**
	 * @param integer|string|array|null $identity   an ID of the record or an array property => $value
	 * @param string|null               $modelClass model instance class name. Autodetect when NULL
	 *
	 * @return ModelInterface
	 */

	public static function getInstance($identity = null, string $modelClass = null)
	{
		static::$instFound = false;
		$instanceClass     = $modelClass ?: get_called_class();

		if ($identity)
		{
			$params = null;

			if (is_array($identity))
			{
				$params = $cond = $bind = [];

				foreach ($identity as $name => $value)
				{
					$cond[]      = $name . ' = :' . $name . ':';
					$bind[$name] = $value;
				}

				$params['conditions'] = implode(' AND ', $cond);
				$params['bind']       = $bind;
			}
			elseif (is_numeric($identity))
			{
				$params = (int) $identity;

				if (strlen((string) $params) !== strlen((string) $identity))
				{
					$params = null;
				}
			}

			if ($params && ($instance = call_user_func($instanceClass . '::findFirst', $params)))
			{
				static::$instFound = true;

				return $instance;
			}
		}

		return new $instanceClass;
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
		return Form::create('filters', MVC_PATH . '/Model/Form/' . $this->getIgnorePrefixModelName() . '/Index/filters.php');
	}

	public function getIgnorePrefixModelName()
	{
		$className = explode('\\', get_class($this));

		return array_pop($className);
	}

	public function getFormsManager()
	{
		$formsManager = new FormsManager;
		$modelName    = $this->getIgnorePrefixModelName();
		$basePath     = MVC_PATH . '/Model/Form/' . $modelName . '/Edit';

		if (is_dir($basePath))
		{
			foreach (FileSystem::scanFiles($basePath) as $form)
			{
				$group = basename($form, '.php');

				if (strcasecmp($modelName, $group) === 0)
				{
					$group = $modelName;
					$name  = $group;
				}
				else
				{
					$name = $modelName . '.' . $group;
				}

				$formsManager->set($group, Form::create($name, $form));
			}
		}

		return $formsManager;
	}

	public function checkin()
	{
		if ($this->id && $this->standardMetadata)
		{
			Service::db()->update(
				$this->getSource(),
				['checkedAt', 'checkedBy'],
				[Date::now('UTC')->toSql(), Auth::id()],
				'id = ' . (int) $this->id
			);
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

		if (isset($data['isDefault']))
		{
			$data['isDefault'] = 'N';
		}

		if (property_exists($this, 'createdAt'))
		{
			$data['createdAt'] = Date::getInstance()->toSql();
		}

		return $entity->assign($data)->save() ? $entity : false;
	}

	public function getTitleField()
	{
		return $this->titleField;
	}

	public function hasStandardMetadata()
	{
		return $this->standardMetadata;
	}

	public function beforeValidation()
	{
		$isNew = empty($this->id);

		foreach ($this->jsonFields as $jsonField)
		{
			$this->{$jsonField} = $this->{$jsonField} ?? '{}';

			if (!is_string($this->{$jsonField}))
			{
				if ($this->{$jsonField} instanceof Registry)
				{
					$this->{$jsonField} = (string) $this->{$jsonField};
				}
				else
				{
					$this->{$jsonField} = json_encode($this->{$jsonField});
				}
			}
		}

		if ($this->standardMetadata)
		{
			$now    = Date::now('UTC')->toSql();
			$author = Auth::id();

			if ($isNew)
			{
				$this->assign(['createdAt' => $now, 'createdBy' => $author]);
			}
			else
			{
				$this->assign(['modifiedAt' => $now, 'modifiedBy' => $author]);
			}
		}
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

	public function t($property)
	{
		$translations = $this->getTranslations();

		if (!empty($translations[$property]))
		{
			return $translations[$property];
		}

		return $this->{$property};
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
		$refTable = preg_replace('/^' . preg_quote($this->modelsManager->getModelPrefix(), '/') . '/', '', $this->getSource(), 1);
		$tranKey  = $refTable . ':' . $refKey . ':' . $language;

		if (!isset($translations[$tranKey . ':array']))
		{
			$entities = Translation::find(
				[
					'conditions' => 'translationId LIKE :translationId:',
					'bind'       => [
						'translationId' => $language . '.' . $refTable . '.id=' . $refKey,
					],
				]
			);

			if ($entities->count())
			{
				foreach ($entities as $entity)
				{
					list($langCode, $refTable, $refKey) = explode('.', $entity->translationId, 3);
					$translatedValue                   = json_decode($entity->translatedValue, true) ?: [];
					$translations[$tranKey . ':value'] = $translatedValue;
					$translations[$tranKey . ':array'] = [
						'language'        => $langCode,
						'refTable'        => $refTable,
						'refKey'          => $refKey,
						'translatedValue' => $translatedValue,
					];
				}
			}
			else
			{
				$translations[$tranKey . ':array'] = [];
				$translations[$tranKey . ':value'] = [];
			}
		}

		return $translations[$asArray ? $tranKey . ':array' : $tranKey . ':value'];
	}

	public function controllerBeforeBindData(&$rawData)
	{
		// Todo something
	}

	public function controllerDoBeforeSave(&$validData, $isNew)
	{
		// Todo something
	}

	public function controllerDoAfterSave($validData, $isNew)
	{
		// Todo something
	}

	public function getI18nData($afterLangCode = ''): array
	{
		$i18n = Registry::create();

		if ($after = trim($afterLangCode, '.'))
		{
			$after = '.' . $after;
		}

		if (Language::isMultilingual())
		{
			foreach (Language::getExistsLanguages() as $langCode => $language)
			{
				if ($translations = $this->getTranslations($langCode))
				{
					$i18n->set($langCode . $after, $translations);
				}
			}
		}

		return $i18n->toArray();
	}

	public function yes(string $property): bool
	{
		return property_exists($this, $property) && $this->{$property} === 'Y';
	}

	public function no(string $property): bool
	{
		return property_exists($this, $property) && $this->{$property} === 'N';
	}

	public function registry(string $property): Registry
	{
		return Registry::create($this->{$property} ?? []);
	}
}
