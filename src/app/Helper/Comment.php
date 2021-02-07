<?php

namespace App\Helper;

use App\Factory\Factory;
use App\Mvc\Model\UcmComment;

class Comment
{
	/** @var string */
	public $referenceContext;

	/** @var integer */
	public $referenceId;

	/** @var integer */
	public $totalItems = 0;

	/** @var array */
	public $items = [];

	public static function getInstance($referenceContext, $referenceId, $offset = 0, $limit = 5): Comment
	{
		static $instances = [];
		$keyHash = $referenceContext . ':' . $referenceId . ':' . $offset . ':' . $limit;

		if (!isset($instances[$keyHash]))
		{
			$referenceClass             = 'App\\Mvc\\Model\\' . UcmItem::prepareContext($referenceContext);
			$queryBuilder               = Service::modelsManager()
				->createBuilder()
				->from(['comment' => UcmComment::class])
				->innerJoin($referenceClass, 'item.id = comment.referenceId', 'item')
				->where('comment.state = :state: AND item.id = :itemId: AND comment.parentId = 0', ['state' => 'P', 'itemId' => $referenceId])
				->andWhere('comment.referenceContext = :context:', ['context' => $referenceContext])
				->orderBy('comment.createdAt ASC')
				->limit($limit, $offset);
			$instance                   = new Comment;
			$instance->referenceContext = $referenceContext;
			$instance->referenceId      = $referenceId;
			$instance->totalItems       = static::getTotalItems($referenceContext, $referenceId);
			$instance->items            = $queryBuilder->getQuery()->execute();
			$instances[$keyHash]        = $instance;
		}

		return $instances[$keyHash];
	}

	public static function getTotalItems($referenceContext, $referenceId)
	{
		return (int) Service::db()
			->fetchColumn('SELECT COUNT(id) FROM ' . Factory::getConfig()->get('db.prefix') . 'ucm_comments WHERE referenceContext = :context AND referenceId = :id AND state = :publish AND parentId = 0',
				[
					'context' => $referenceContext,
					'id'      => $referenceId,
					'publish' => 'P',
				]
			);
	}

}