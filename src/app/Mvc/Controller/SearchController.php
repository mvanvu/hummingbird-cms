<?php

namespace App\Mvc\Controller;

use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;
use App\Mvc\Model\UcmItem as Item;
use App\Mvc\Model\UcmItemMap;
use App\Mvc\Model\Translation;
use App\Mvc\Model\Tag;
use App\Helper\Config;
use App\Helper\Event;
use App\Helper\Language;

class SearchController extends ControllerBase
{
	public function resultsAction()
	{
		$contexts     = ['post'];
		$queryBuilder = $this->modelsManager
			->createBuilder()
			->from(['item' => Item::class])
			->where('item.state = :p:', ['p' => 'P']);
		$language     = Language::getLanguageQuery();
		$hasQuery     = false;

		if ($q = $this->request->get('q', ['trim', 'string'], null))
		{
			$hasQuery   = true;
			$q          = strtolower($q);
			$andWhere   = 'LOWER(item.title) LIKE :q: OR LOWER(item.summary) LIKE :q: OR LOWER(item.description) LIKE :q: OR LOWER(item.metaTitle) LIKE :q: OR LOWER(item.metaKeys) LIKE :q: OR LOWER(item.metaDesc) LIKE :q:';
			$bindParams = [
				'q' => '%' . $q . '%',
			];

			if ('*' !== $language)
			{
				$tranQuery = $this->modelsManager
					->createBuilder()
					->columns('translationId')
					->from(Translation::class)
					->where('LOWER(translatedValue) LIKE :q:', ['q' => '%' . $q . '%']);
				$tranWhere = [];
				$bindKeys  = [
					'likeKey0' => $language . '.ucm_items.id=%.title',
					'likeKey1' => $language . '.ucm_items.id=%.summary',
					'likeKey2' => $language . '.ucm_items.id=%.description',
					'likeKey3' => $language . '.ucm_items.id=%.metaTitle',
					'likeKey4' => $language . '.ucm_items.id=%.metaKeys',
					'likeKey5' => $language . '.ucm_items.id=%.metaDesc',
					'likeKey6' => $language . '.ucm_items.id=%.route',
				];

				foreach ($bindKeys as $bindKey => $bindValue)
				{
					$tranWhere[] = 'translationId LIKE :' . $bindKey . ':';
				}

				$tranQuery->orWhere('(' . implode(' OR ', $tranWhere) . ')', $bindKeys);
				$tranKeys = $tranQuery->getQuery()->execute();
				$itemIds  = [];

				if ($tranKeys->count())
				{
					foreach ($tranKeys as $tranKey)
					{
						$parts     = explode('.', $tranKey->translationId);
						$itemIds[] = (int) str_replace('id=', '', $parts[2]);
					}
				}

				if ($itemIds)
				{
					$andWhere              .= ' OR item.id IN({itemIds:array})';
					$bindParams['itemIds'] = $itemIds;
				}
			}

			$queryBuilder->andWhere('(' . $andWhere . ')', $bindParams);
		}

		if ($tag = $this->request->get('tag', ['string', 'trim'], ''))
		{
			$hasQuery  = true;
			$tagsArray = strpos($tag, '|') ? array_unique(explode('|', $tag)) : [$tag];

			if ('*' !== $language)
			{
				$bindData  = [
					'translationId' => $language . '.tags.id=%.slug',
					'tags'          => $tagsArray,
				];
				$tranQuery = $this->modelsManager
					->createBuilder()
					->columns(['tran.originalValue', 'tran.translatedValue'])
					->from(['tran' => Translation::class])
					->where('tran.translationId LIKE :translationId:', $bindData)
					->andWhere('tran.translatedValue IN ({tags:array})');
				$tranTags  = $tranQuery->getQuery()->execute();

				if ($tranTags->count())
				{
					foreach ($tranTags as $tranTag)
					{
						if (false !== ($search = array_search($tranTag->translatedValue, $tagsArray)))
						{
							unset($tagsArray[$search]);
						}

						if (!in_array($tranTag->originalValue, $tagsArray))
						{
							$tagsArray[] = $tranTag->originalValue;
						}
					}

					$tagsArray = array_values($tagsArray);
				}
			}

			$queryBuilder->innerJoin(UcmItemMap::class, 'tagMap.itemId1 = item.id AND tagMap.context = :tagContext:', 'tagMap')
				->innerJoin(Tag::class, 'tag.id = tagMap.itemId2', 'tag')
				->andWhere('tag.slug IN ({tags:array})', ['tags' => $tagsArray, 'tagContext' => 'tag']);

		}

		Event::trigger('onBeforeSearchQuery', [$queryBuilder, &$contexts, &$hasQuery], ['Cms']);
		$queryBuilder->andWhere('item.context IN ({contexts:array})', ['contexts' => $contexts]);

		if ($hasQuery)
		{
			$paginator = new Paginator(
				[
					'builder' => $queryBuilder,
					'limit'   => Config::get('listLimit', 20),
					'page'    => $this->request->get('page', ['uint'], 0),
				]
			);

			$this->view->setVar('paginator', $paginator);
		}

		$this->view->pick('Search/Result');
	}
}