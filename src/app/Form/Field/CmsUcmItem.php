<?php

namespace App\Form\Field;

use App\Helper\Constant;
use App\Helper\Service;
use App\Helper\UcmItem as UcmItemHelper;
use App\Mvc\Model\Nested;
use App\Mvc\Model\UcmItem;
use MaiVu\Php\Form\Field\Select;
use Phalcon\Mvc\Dispatcher;

class CmsUcmItem extends Select
{
	protected $context = null;

	public function getOptions()
	{
		/**
		 * @var Nested     $class
		 * @var Dispatcher $dispatcher
		 */

		$dispatcher = Service::dispatcher();
		$options    = [];

		if (empty($this->context))
		{
			$this->context = $dispatcher->getParam('context');
		}

		$modelClass = Constant::NAMESPACE_MODEL . '\\' . UcmItemHelper::prepareContext($this->context);

		if (class_exists($modelClass))
		{
			$class = new $modelClass;

			if ($class instanceof Nested)
			{
				$excludes    = [];
				$isEditItem  = 'admin_ucm_item' === $dispatcher->getControllerName();
				$itemContext = $dispatcher->getParam('context');

				if ($isEditItem
					&& $this->context === $itemContext
					&& ($id = $dispatcher->getParam('id', 'int', 0))
				)
				{
					$excludes[] = $id;

					foreach ($class->getTree($id) as $node)
					{
						$excludes[] = $node->id;
					}
				}

				$rootId     = $class->getRootId();
				$excludes[] = $rootId;
				$options[]  = [
					'value' => $isEditItem && $class->isContextSuffix('category', $itemContext) ? $rootId : '0',
					'text'  => 'no-parent',
				];

				foreach ($class->getTree($rootId) as $node)
				{
					if (!in_array($node->id, $excludes))
					{
						$options[] = [
							'value' => $node->id,
							'text'  => str_repeat('-', $node->level - 1) . ' ' . $node->title,
						];
					}
				}
			}
			else
			{
				$entities = UcmItem::find(
					[
						'conditions' => 'state = :state: AND context = :context:',
						'bind'       => [
							'state'   => 'P',
							'context' => $this->context,
						],
					]
				);

				foreach ($entities as $entity)
				{
					$options[] = [
						'value' => $entity->id,
						'text'  => $entity->title,
					];
				}
			}
		}

		return array_merge(parent::parseOptions($this->options), $options);
	}
}
