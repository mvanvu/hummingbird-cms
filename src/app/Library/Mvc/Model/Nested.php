<?php

namespace MaiVu\Hummingbird\Lib\Mvc\Model;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Db\Enum;
use stdClass;

class Nested extends UcmItem
{
	private $isNew = true;

	public function getRootId()
	{
		static $rootId = null;

		if (null === $rootId)
		{
			/** @var Mysql $db */
			$db     = $this->getDI()->get('db');
			$source = $this->getSource();
			$root   = $db->fetchOne('SELECT id FROM ' . $source . ' WHERE context = :context AND parentId = 0 AND title = \'system-node-root\'',
				Enum::FETCH_OBJ,
				[
					'context' => $this->context,
				]
			);

			if (empty($root->id))
			{
				$db->execute('INSERT INTO ' . $source . '(context, title, state, lft, rgt) VALUES (:context, \'system-node-root\', \'P\', 0, 1)',
					[
						'context' => $this->context,
					]
				);

				$rootId = (int) $db->lastInsertId();
			}
			else
			{
				$rootId = (int) $root->id;
			}
		}

		return $rootId;
	}

	public function beforeSave()
	{
		$this->isNew = empty($this->id);

		if ((int) $this->parentId < 1)
		{
			$this->parentId = $this->getRootId();
		}

		if (!$this->isNew)
		{
			$this->moveNodeToNewParent($this->parentId);
		}

		return parent::beforeSave();
	}

	public function afterSave()
	{
		if ($this->isNew)
		{
			$this->handleAddNewNode();
		}

		return true;
	}

	public function getTree($rootId = null)
	{
		/** @var Mysql $db */
		$db     = $this->getDI()->get('db');
		$source = $this->getSource();

		if (null === $rootId)
		{
			$rootId = $this->id;
		}

		return $db->fetchAll('SELECT n.* FROM ' . $source . ' AS n, ' . $source . ' AS p WHERE n.lft BETWEEN p.lft AND p.rgt AND n.context = :context AND n.state = :state AND p.id = :rootId GROUP BY n.id ORDER BY n.lft',
			Enum::FETCH_OBJ,
			[
				'state'   => 'P',
				'rootId'  => $rootId,
				'context' => $this->context,
			]
		);
	}

	public function getParentTree()
	{
		/** @var Mysql $db */
		$db     = $this->getDI()->get('db');
		$source = $this->getSource();

		return $db->fetchAll('SELECT * FROM ' . $source . ' WHERE lft < :lft AND rgt > :rgt AND context = :context ORDER BY lft',
			Enum::FETCH_OBJ,
			[
				'lft'     => $this->lft,
				'rgt'     => $this->rgt,
				'context' => $this->context,
			]
		);
	}

	public function fix()
	{
		/** @var Mysql $db */
		$db     = $this->getDI()->get('db');
		$source = $this->getSource();
		$rootId = $this->getRootId();
		$db->execute('UPDATE ' . $source . ' SET level = 0 WHERE id = :rootId',
			[
				'rootId' => $rootId,
			]
		);
		$db->execute('UPDATE ' . $source . ' SET parentId = :rootId, level = 1 WHERE context = :context AND id <> :rootId AND (parentId = id OR parentId = 0)',
			[
				'rootId'  => $rootId,
				'context' => $this->context,
			]
		);
	}

	public function rebuild($nodeId = null, $leftId = 0, $level = 0)
	{
		if (null === $nodeId)
		{
			$nodeId = $this->getRootId();
		}

		/** @var Mysql $db */
		$db       = $this->getDI()->get('db');
		$source   = $this->getSource();
		$rightId  = $leftId + 1;
		$children = $db->fetchAll('SELECT id FROM ' . $source . ' WHERE context = :context AND parentId = :parentId ORDER BY parentId, lft',
			Enum::FETCH_OBJ,
			[
				'parentId' => $nodeId,
				'context'  => $this->context,
			]
		);

		if (!empty($children))
		{
			foreach ($children as $child)
			{
				$rightId = $this->rebuild($child->id, $rightId, $level + 1);
			}
		}

		$db->execute('UPDATE ' . $source . ' SET lft = :lft, rgt = :rgt, level = :level WHERE context = :context AND id = :nodeId',
			[
				'lft'     => $leftId,
				'rgt'     => $rightId,
				'nodeId'  => $nodeId,
				'level'   => $level,
				'context' => $this->context,
			]
		);

		return $rightId + 1;
	}

	public function getNode($nodeId)
	{
		/**
		 * @var Mysql     $db
		 * @var stdClass $node
		 */
		$db     = $this->getDI()->get('db');
		$source = $this->getSource();
		$node   = $db->fetchOne('SELECT id, lft, rgt, parentId, level FROM ' . $source . ' WHERE id = :nodeId',
			Enum::FETCH_OBJ,
			[
				'nodeId' => $nodeId,
			]
		);

		if ($node)
		{
			$node->width = (int) $node->rgt - (int) $node->lft + 1;
		}

		return $node;
	}

	protected function handleAddNewNode()
	{
		/** @var Mysql $db */
		$db     = $this->getDI()->get('db');
		$source = $this->getSource();

		// Lock the table
		$db->execute('LOCK TABLE ' . $source . ' WRITE');
		$parent   = $this->getNode($this->parentId);
		$newLevel = (int) $parent->level + 1;
		$newLft   = (int) $parent->rgt;
		$newRgt   = $newLft + 1;

		// make a gap in tree
		$db->execute('UPDATE ' . $source . ' SET rgt = rgt + 2 WHERE context = :context AND rgt >= :newLft',
			[
				'context' => $this->context,
				'newLft'  => $newLft,
			]
		);
		$db->execute('UPDATE ' . $source . ' SET lft = lft + 2 WHERE context = :context AND lft > :newLft',
			[
				'context' => $this->context,
				'newLft'  => $newLft,
			]
		);

		// Update itself
		$db->execute('UPDATE ' . $source . ' SET lft = :lft, rgt = :rgt, level = :level WHERE id = :id',
			[
				'lft'   => $newLft,
				'rgt'   => $newRgt,
				'level' => $newLevel,
				'id'    => $this->id,
			]
		);


		// Unlock tables
		$db->execute('UNLOCK TABLES');
		$this->lft   = $newLft;
		$this->rgt   = $newRgt;
		$this->level = $newLevel;
	}

	public function moveNodeToNewParent($toParentId)
	{
		$node   = $this->getNode($this->id);
		$parent = $this->getNode($toParentId);

		if (empty($node)
			|| empty($parent)
			|| $node->width < 2
		)
		{
			return false;
		}

		if ($node->parentId == $parent->id)
		{
			// Node has the same parent, so we done here
			return true;
		}

		/** @var Mysql $db */
		$db     = $this->getDI()->get('db');
		$source = $this->getSource();

		// Lock the table
		$db->execute('LOCK TABLE ' . $source . ' WRITE');

		$oldLft = (int) $node->lft;
		$oldRgt = (int) $node->rgt;
		$width  = (int) $node->width;

		// Get the ids of child nodes.
		$children = $db->fetchAll('SELECT id FROM ' . $source . ' WHERE context = :context AND lft BETWEEN :lft AND :rgt',
			Db::FETCH_OBJ,
			[
				'context' => $this->context,
				'lft'     => $oldLft,
				'rgt'     => $oldRgt,
			]
		);

		foreach ($children as $child)
		{
			if ($child->id == $toParentId)
			{
				// Unlock tables
				$db->execute('UNLOCK TABLES');

				return false;
			}
		}

		// Move the sub-tree out of the nested sets by negating its left and right values.
		$db->execute('UPDATE ' . $source . ' SET lft = lft * (-1), rgt = rgt * (-1) WHERE context = :context AND lft BETWEEN :lft AND :rgt',
			[
				'context' => $this->context,
				'lft'     => $oldLft,
				'rgt'     => $oldRgt,
			]
		);

		// Compress the right values.
		$db->execute('UPDATE ' . $source . ' SET lft = lft - :width WHERE context = :context AND lft > :oldRgt',
			[
				'context' => $this->context,
				'width'   => $width,
				'oldRgt'  => $oldRgt,
			]
		);
		$db->execute('UPDATE ' . $source . ' SET rgt = rgt - :width WHERE context = :context AND rgt > :oldRgt',
			[
				'context' => $this->context,
				'width'   => $width,
				'oldRgt'  => $oldRgt,
			]
		);

		// Get new updated parent node
		$parent    = $this->getNode($toParentId);
		$parentRgt = (int) $parent->rgt;
		$newLft    = $parentRgt;
		$newRgt    = $parentRgt + $width - 1;
		$offset    = $newLft - $oldLft;

		// Shift left values.
		$db->execute('UPDATE ' . $source . ' SET lft = lft + :width WHERE context = :context AND lft > :parentRgt',
			[
				'context'   => $this->context,
				'width'     => $width,
				'parentRgt' => $parentRgt,
			]
		);
		$db->execute('UPDATE ' . $source . ' SET rgt = rgt + :width WHERE context = :context AND rgt >= :parentRgt',
			[
				'context'   => $this->context,
				'width'     => $width,
				'parentRgt' => $parentRgt,
			]
		);

		// Move the nodes back into position in the tree using the calculated offsets.
		$db->execute('UPDATE ' . $source . ' SET rgt = :offset - rgt, lft = :offset - lft WHERE context = :context AND lft < 0',
			[
				'context' => $this->context,
				'offset'  => $offset,
			]
		);

		// Update itself
		$this->lft   = $newLft;
		$this->rgt   = $newRgt;
		$this->level = $parent->level + 1;

		$db->execute('UPDATE ' . $source . ' SET lft = :lft, rgt = :rgt, level = :level WHERE id = :id',
			[
				'lft'   => $this->lft,
				'rgt'   => $this->rgt,
				'level' => $this->level,
				'id'    => $this->id,
			]
		);

		// Unlock tables
		$db->execute('UNLOCK TABLES');
	}

	public function modifyNode($nodeId, $state)
	{
		$node = $this->getNode($nodeId);

		if (empty($node) || !in_array($state, ['P', 'U', 'T', 'unlock']))
		{
			return false;
		}

		if ('unlock' === $state)
		{
			/** @var UcmItem $entity */
			$entity = static::findFirst('id = ' . (int) $nodeId);

			if (!$entity || !$entity->checkout())
			{
				return false;
			}

			return true;
		}

		/** @var Mysql $db */
		$db     = $this->getDI()->get('db');
		$source = $this->getSource();
		$lft    = (int) $node->lft;
		$rgt    = (int) $node->rgt;
		$width  = (int) $node->width;

		// Lock the table
		$db->execute('LOCK TABLE ' . $source . ' WRITE');

		if ('T' === $state)
		{
			// Delete the node and all of its children.
			$db->execute('DELETE FROM  ' . $source . ' WHERE context = :context AND lft BETWEEN :lft AND :rgt',
				[
					'context' => $this->context,
					'lft'     => $lft,
					'rgt'     => $rgt,
				]
			);

			// Compress the right values.
			$db->execute('UPDATE ' . $source . ' SET lft = lft - :width WHERE context = :context AND lft > :rgt',
				[
					'context' => $this->context,
					'width'   => $width,
					'rgt'     => $rgt,
				]
			);
			$db->execute('UPDATE ' . $source . ' SET rgt = rgt - :width WHERE context = :context AND rgt > :rgt',
				[
					'context' => $this->context,
					'width'   => $width,
					'rgt'     => $rgt,
				]
			);
		}
		else
		{
			// Update state node and all its children
			$db->execute('UPDATE  ' . $source . ' SET state = :state WHERE context = :context AND lft BETWEEN :lft AND :rgt',
				[
					'context' => $this->context,
					'lft'     => $lft,
					'rgt'     => $rgt,
					'state'   => $state,
				]
			);
		}

		// Unlock tables
		$db->execute('UNLOCK TABLES');

		return true;
	}
}