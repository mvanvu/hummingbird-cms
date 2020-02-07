<?php

namespace MaiVu\Hummingbird\Widget\FlashNews;

use Phalcon\Paginator\Adapter\QueryBuilder as Paginator;
use MaiVu\Hummingbird\Lib\Factory;
use MaiVu\Hummingbird\Lib\Widget;
use MaiVu\Hummingbird\Lib\Mvc\Model\Post;
use MaiVu\Hummingbird\Lib\Mvc\Model\PostCategory;

class FlashNews extends Widget
{
	public function getContent()
	{
		$cid      = $this->widget->get('params.categoryIds', []);
		$postsNum = $this->widget->get('params.postsNum', 5, 'uint');

		if (count($cid))
		{
			$bindIds = [];
			$nested  = new PostCategory;

			foreach ($cid as $id)
			{
				if ($tree = $nested->getTree((int) $id))
				{
					foreach ($tree as $node)
					{
						$bindIds[] = (int) $node->id;
					}
				}
			}

			if (empty($bindIds))
			{
				return null;
			}

			$queryBuilder = Post::query()
				->createBuilder()
				->from(['post' => Post::class])
				->where('post.parentId IN ({cid:array})', ['cid' => array_unique($bindIds)])
				->andWhere('post.state = :state:', ['state' => 'P']);

			switch ($this->widget->get('params.orderBy', 'latest'))
			{
				case 'random':
					$queryBuilder->orderBy('RAND()');
					break;

				case 'titleAsc':
					$queryBuilder->orderBy('title asc');
					break;

				case 'titleDesc':
					$queryBuilder->orderBy('title desc');
					break;

				default:
					$queryBuilder->orderBy('createdAt desc');
					break;
			}

			// Init renderer
			$renderer = $this->getRenderer();
			$partial  = 'Content/' . $this->getPartialId();

			if ('BlogList' === $this->widget->get('params.displayLayout', 'FlashNews'))
			{
				$paginator = new Paginator(
					[
						'builder' => $queryBuilder,
						'limit'   => $postsNum,
						'page'    => Factory::getService('request')->get('page', ['absint'], 0),
					]
				);

				$paginate = $paginator->paginate();

				if ($paginate->getTotalItems())
				{
					return $renderer->getPartial(
						$partial,
						[
							'posts'      => $paginate->getItems(),
							'pagination' => Factory::getService('view')->getPartial('Pagination/Pagination',
								[
									'paginator' => $paginator,
								]
							),
						]
					);
				}
			}
			else
			{
				$posts = $queryBuilder->limit($postsNum, 0)->getQuery()->execute();

				if ($posts->count())
				{
					return $renderer->getPartial($partial, ['posts' => $posts]);
				}
			}
		}

		return null;
	}
}