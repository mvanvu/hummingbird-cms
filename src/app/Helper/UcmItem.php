<?php

namespace App\Helper;

use App\Mvc\Model\UcmItem as ItemModel;
use MaiVu\Php\Registry;

class UcmItem
{
	public static function prepareContext($context, $asArray = false)
	{
		$context = array_map('ucfirst', explode('-', $context));

		return $asArray ? $context : implode('', $context);
	}

	public static function parseSortBySql(string $sortBy = null, string $alias = ''): string
	{
		if ($alias && strpos($alias, '.') !== 0)
		{
			$alias .= '.';
		}

		switch ($sortBy)
		{
			case 'random':
				return 'RAND()';

			case 'titleAsc':
				return $alias . 'title asc';

			case 'titleDesc':
				return $alias . 'title desc';

			case 'ordering':
				return $alias . 'ordering asc';

			default:
				return $alias . 'createdAt desc';
		}
	}

	public static function parseCategoryParams(ItemModel $category): Registry
	{
		$params = $category->getParams();

		foreach ($params->toArray() as $name => $value)
		{
			$parent = $category->getParent();

			while (empty($value) && '0' != $value && $parent)
			{
				$value  = $parent->getParams()->get($name);
				$parent = $parent->getParent();
			}

			$params->set($name, $value);
		}

		return $params;
	}
}