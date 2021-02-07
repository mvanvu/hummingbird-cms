<?php


namespace App\Mvc\Model;

use Exception;
use Phalcon\Mvc\Model\Binder;

class ModelBinder extends Binder
{
	/**
	 * Find the model by param value.
	 *
	 * @param mixed  $paramValue
	 * @param string $className
	 *
	 * @return object|false
	 * @throws Exception
	 */

	protected function findBoundModel($paramValue, string $className)
	{
		return $paramValue
			? ModelBase::getInstanceOrFail($paramValue, $className)
			: ModelBase::getInstance($paramValue, $className);
	}
}