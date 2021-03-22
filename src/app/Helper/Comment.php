<?php

namespace App\Helper;

use App\Mvc\Model\UcmComment;

class Comment
{
	/**
	 * @var string
	 */
	public $referenceContext;

	/**
	 * @var integer
	 */
	public $referenceId;

	/**
	 * @var integer
	 */
	public $totalLines = 0;

	/**
	 * @var integer
	 */
	public $totalItems = 0;

	/**
	 * @var array
	 */
	public $items = [];

	public static function getInstance($referenceContext, $referenceId, $offset = 0, $limit = 5): Comment
	{
		static $instances = [];
		$keyHash = $referenceContext . ':' . $referenceId . ':' . $offset . ':' . $limit;

		if (!isset($instances[$keyHash]))
		{
			$referenceClass             = Constant::NAMESPACE_MODEL . '\\' . UcmItem::prepareContext($referenceContext);
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
			$instance->totalLines       = static::getTotalLines($referenceContext, (int) $referenceId);
			$instance->totalItems       = static::getTotalItems($referenceContext, (int) $referenceId);
			$instance->items            = $queryBuilder->getQuery()->execute();
			$instances[$keyHash]        = $instance;
		}

		return $instances[$keyHash];
	}

	public static function getTotalLines(string $referenceContext, int $referenceId)
	{
		return (int) UcmComment::count(
			[
				'conditions' => 'referenceContext = :context: AND referenceId = :id: AND state = \'P\' AND parentId < 1',
				'bind'       => [
					'context' => $referenceContext,
					'id'      => $referenceId,
				]
			]
		);
	}

	public static function getTotalItems(string $referenceContext, int $referenceId)
	{
		return (int) UcmComment::count(
			[
				'conditions' => 'referenceContext = :context: AND referenceId = :id: AND state = \'P\'',
				'bind'       => [
					'context' => $referenceContext,
					'id'      => $referenceId,
				]
			]
		);
	}
}