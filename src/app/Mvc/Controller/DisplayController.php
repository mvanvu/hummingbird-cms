<?php

namespace App\Mvc\Controller;

use App\Helper\Constant;
use App\Helper\Event;
use App\Helper\Image;
use App\Helper\Language;
use App\Helper\MetaData;
use App\Helper\State;
use App\Helper\StringHelper;
use App\Helper\Template;
use App\Helper\Text;
use App\Helper\UcmItem as UcmItemHelper;
use App\Helper\Uri;
use App\Mvc\Model\Nested;
use App\Mvc\Model\Translation;
use App\Mvc\Model\UcmItem as UcmItemModel;

class DisplayController extends ControllerBase
{
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
					'conditions' => 'translationId LIKE :translationId: AND JSON_EXTRACT(translatedValue, \'$.route\') = :route:',
					'bind'       => [
						'translationId' => $language . '.ucm_items.id=%',
						'route'         => $bindParams['route'],
					],
				]
			);

			if ($translation)
			{
				$bindParams['route'] = json_decode($translation->translatedValue, true)['route'];
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
		$targetClass = Constant::NAMESPACE_MODEL . '\\' . $context;

		if (!class_exists($targetClass) || !($targetItem = $targetClass::findFirst('id = ' . $result->id)))
		{
			return $this->notFound();
		}

		// Metadata
		$meta = MetaData::getInstance();
		$meta->setTitle($targetItem->t('metaTitle') ?: $targetItem->t('title'));
		$meta->setDescription($targetItem->t('metaDesc') ?: StringHelper::truncate($targetItem->t('description'), 160));
		$meta->setKeys($targetItem->t('metaKeys'));

		if ($link = $targetItem->getLink())
		{
			$meta->addTag('base', ['href' => $link]);
			$meta->addTag('meta', ['property' => 'og:url', 'content' => $link]);
		}

		if ($image = Image::loadImage($targetItem->image))
		{
			$meta->addTag('meta', ['property' => 'og:image', 'content' => $image->getUri()]);
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
			'breadcrumbs' => $breadcrumbs,
			$itemAlias    => $targetItem,
		];

		$templates  = Template::getTemplates();
		$templateId = $targetItem->getParams()->get('templateId', '');
		$parent     = $targetItem->getParent();

		while ('' === $templateId && $parent)
		{
			$templateId = $parent->getParams()->get('templateId', '');
			$parent     = $parent->getParent();
		}

		$newTemplatePath = APP_PATH . '/Tmpl/Site/Template-' . $templateId . '/';

		if ($templateId
			&& isset($templates[$templateId])
			&& is_dir($newTemplatePath)
		)
		{
			$viewDirs = $this->view->getViewsDir();

			if ($viewDirs[0] === TPL_SITE_PATH . '/')
			{
				array_shift($viewDirs);
			}

			array_unshift($viewDirs, $newTemplatePath);
			$this->view->setViewsDir($viewDirs);
		}

		// Update hits
		$targetItem->hits();
		Event::trigger('onBeforeDisplayUcmItem', [$this, $targetItem], ['Cms']);
		State::setMark('displayUcmItem', $targetItem);
		$this->view->setVars($vars);
		$this->view->pick($context . '/Show');
	}
}
