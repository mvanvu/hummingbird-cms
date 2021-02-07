<?php

namespace App\Helper;

use App\Mvc\Model\Config as ConfigModel;
use MaiVu\Php\Registry;

class Config
{
	protected static $dataContexts = [];

	/**
	 * @param string $context
	 *
	 * @return Registry
	 */

	public static function setDataContext($context, Registry $configData)
	{
		static::$dataContexts[$context] = $configData;
	}

	public static function is($index, $context = 'cms.config')
	{
		return static::getByContext($context)->get($index) === 'Y';
	}

	public static function getByContext($context = 'cms.config')
	{
		if (!isset(static::$dataContexts[$context]))
		{
			if ($entity = static::getEntity($context))
			{
				$data = json_decode($entity->data, true) ?: [];
			}
			else
			{
				$data = [];
			}

			static::$dataContexts[$context] = new Registry($data);
		}

		return static::$dataContexts[$context];
	}

	/**
	 * @param $context
	 *
	 * @return ConfigModel
	 */

	public static function getEntity($context)
	{
		static $entities = [];

		if (!isset($entities[$context]))
		{
			$eval   = strpos($context, '%') === false ? '=' : 'LIKE';
			$entity = ConfigModel::findFirst(
				[
					'conditions' => 'context ' . $eval . ' :context:',
					'bind'       => [
						'context' => $context,
					],
				]
			);

			if (!$entity)
			{
				$entity          = new ConfigModel;
				$entity->context = $context;
			}

			$entities[$context] = $entity;
		}

		return $entities[$context];
	}

	/**
	 * @param null   $index
	 * @param null   $default
	 * @param string $context
	 *
	 * @return mixed|Registry
	 */

	public static function get($index = null, $default = null, $context = 'cms.config')
	{
		$config = static::getByContext($context);

		return null === $index ? $config : $config->get($index, $default);
	}
}
