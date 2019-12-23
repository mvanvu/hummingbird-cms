<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Controller;

use MaiVu\Hummingbird\Lib\Helper\Config;
use MaiVu\Hummingbird\Lib\Helper\Event;
use MaiVu\Hummingbird\Lib\Helper\StringHelper;
use MaiVu\Hummingbird\Lib\Helper\Text;
use MaiVu\Hummingbird\Lib\Helper\Uri;
use MaiVu\Hummingbird\Lib\Helper\State;
use MaiVu\Hummingbird\Lib\Helper\Language;
use MaiVu\Hummingbird\Lib\Helper\UcmItem as UcmItemHelper;
use MaiVu\Hummingbird\Lib\Mvc\Model\UcmItem as UcmItemModel;
use MaiVu\Hummingbird\Lib\Mvc\Model\Nested;
use MaiVu\Hummingbird\Lib\Mvc\Model\Translation;
use stdClass;

class DisplayController extends ControllerBase
{
	protected function notFound()
	{
		$this->dispatcher->forward(
			[
				'controller' => 'error',
				'action'     => 'show',
			]
		);

		return false;
	}

	public function showAction()
	{
		/** @var UcmItemModel $ucmItem */
		$params = $this->dispatcher->getParams();
		
		if (isset($params[0]) && strpos($params[0], '?') !== 0)
		{
			return $this->notFound();
		}

		$language     = Language::getLanguageQuery();
		$queryBuilder = $this->modelsManager
			->createBuilder()
			->columns('id, context')
			->from(UcmItemModel::class)
			->where('state = :state:')
			->andWhere('route = :route:');
		$bindParams   = [
			'route' => $this->dispatcher->getParam('path'),
			'state' => 'P',
		];

		if ('*' !== $language)
		{
			// We're in multilingual mode
			$translation = Translation::findFirst(
				[
					'conditions' => 'translationId LIKE :translationId: AND translatedValue = :route:',
					'bind'       => [
						'translationId' => $language . '.ucm_items.id=%.route',
						'route'         => $bindParams['route'],
					],
				]
			);

			if ($translation && $translation->originalValue)
			{
				$bindParams['route'] = $translation->originalValue;
			}
		}

		$result = $queryBuilder->getQuery()
			->execute($bindParams)
			->getFirst();

		if (!$result || empty($result->context))
		{
			return $this->notFound();
		}

		/** @var UcmItemModel $targetItem */
		$context     = UcmItemHelper::prepareContext($result->context);
		$targetClass = 'MaiVu\\Hummingbird\\Lib\\Mvc\\Model\\' . $context;

		if (!class_exists($targetClass)
			|| !($targetItem = $targetClass::findFirst(['conditions' => 'id = ' . $result->id]))
		)
		{
			return $this->notFound();
		}

		// Metadata
		$metadata                = new stdClass;
		$metadata->metaTitle     = $targetItem->t('metaTitle');
		$metadata->metaDesc      = $targetItem->t('metaDesc');
		$metadata->metaKeys      = $targetItem->t('metaKeys');
		$metadata->contentRights = Config::get('siteContentRights');
		$metadata->metaRobots    = Config::get('siteRobots');

		if (empty($metadata->metaTitle))
		{
			$metadata->metaTitle = $targetItem->t('title');
		}

		if (empty($metadata->metaDesc))
		{
			$metadata->metaDesc = StringHelper::truncate($targetItem->t('description'), 160);
		}

		$parent = $targetItem->getParent();

		if ($targetItem instanceof Nested)
		{
			$rootId = $targetItem->getRootId();
		}
		elseif ($parent instanceof Nested)
		{
			$rootId = $parent->getRootId();
		}
		else
		{
			$rootId = 0;
		}

		$breadcrumbs = [];

		while ($parent)
		{
			if ((int) $parent->id !== $rootId)
			{
				$breadcrumbs[] = [
					'link'  => $parent->getLink(),
					'title' => $parent->t('title'),
				];
			}

			$parent = $parent->getParent();
		}

		$itemAlias   = lcfirst($context);
		$breadcrumbs = array_reverse($breadcrumbs);
		array_unshift($breadcrumbs,
			[
				'link'  => Uri::home(),
				'title' => Text::_('home'),
			]
		);
		$breadcrumbs[] = [
			'link'  => null,
			'title' => $targetItem->t('title'),
		];
		$vars          = [
			'metadata'    => $metadata,
			'breadcrumbs' => $breadcrumbs,
			$itemAlias    => $targetItem,
		];

		// Update hits
		$targetItem->hit();
		State::setMark('displayUcmItem', $targetItem);
		Event::trigger('beforeDisplayUcm' . $context, [$this, $targetItem]);
		$this->view->setVars($vars);
		$this->view->pick($context . '/Show');
	}
}
