<?php

namespace App\Helper;

use App\Factory\Factory;
use App\Helper\User as Auth;
use Phalcon\Db\Enum;
use Phalcon\Paginator\RepositoryInterface;

class Nested
{
	/** @var Uri */
	protected $uri;

	/** @var array */
	protected $sources;

	/** @var integer */
	protected $count = 0;

	public function __construct(string $context, Uri $uri = null, RepositoryInterface $sources = null)
	{
		$this->uri = $uri;
		$this->setSources($context, $sources);
		Assets::jQueryCore();
	}

	public function setSources(string $context, RepositoryInterface $sources = null)
	{
		if (null === $sources)
		{
			$this->count = 0;
		}
		else
		{
			$this->count = $sources->getTotalItems();
		}

		$rootId        = static::getRootId($context);
		$this->sources = [
			'i' => [],
			'p' => [],
		];

		foreach ($sources->getItems() as $source)
		{
			if ($source->parentId && $source->parentId != $rootId)
			{
				$this->sources['p'][$source->parentId][] = $source;
			}
			else
			{
				$this->sources['i'][] = $source;
			}
		}

		return $this;
	}

	public static function getRootId(string $context): int
	{
		static $rootId = null;

		if (null === $rootId)
		{
			$source = Database::table('ucm_items');
			$db     = Service::db();
			$root   = $db->fetchOne('SELECT id FROM ' . $source . ' WHERE context = :context AND parentId = 0 AND title = \'system-node-root\'',
				Enum::FETCH_OBJ,
				[
					'context' => $context,
				]
			);

			if (empty($root->id))
			{
				$db->execute('INSERT INTO ' . $source . '(context, title, state, lft, rgt, createdAt, createdBy) VALUES (:context, \'system-node-root\', \'P\', 0, 1, :createdAt, :createdBy)',
					[
						'context'   => $context,
						'createdAt' => Date::now('UTC')->toSql(),
						'createdBy' => Auth::id(),
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

	public function makeTree(array $items = null)
	{
		$tree = '<ol class="dd-list">';

		if (null === $items)
		{
			$items = $this->sources['i'];
		}

		foreach ($items as $item)
		{
			$isNotRoot   = $item->title != 'system-node-root';
			$hasChildren = !empty($this->sources['p'][$item->id]);
			$title       = htmlspecialchars($item->title);

			if ($isNotRoot)
			{
				$tree .= '<li class="dd-item ' . ($hasChildren ? 'has-children' : 'no-children') . '" data-id="' . $item->id . '" data-title="' . $title . '">'
					. $this->makeHandle($item);
			}

			if ($hasChildren)
			{
				$tree .= $this->makeTree($this->sources['p'][$item->id]);
			}

			if ($isNotRoot)
			{
				$tree .= '</li>';
			}
		}

		$tree .= '</ol>';

		return $tree;
	}

	protected function makeHandle($item)
	{
		$title = $item->title . '&nbsp;<em class="uk-text-muted uk-visible@s">' . $item->route . '</em>';
		$state = $item->state;

		if ($item->isCheckedIn())
		{
			$title = '<a class="dd-nodrag uk-link-reset uk-display-inline-block" data-action="unlock" href="">' . Factory::getService('view')->getPartial('Grid/CheckedIn', ['item' => $item, 'title' => $title]) . '</a>';
		}
		else
		{
			$title = '<a class="dd-nodrag uk-link-reset uk-display-inline-block" href="' . $this->uri->routeTo('edit/' . $item->id) . '">' . $title . '</a>';
		}

		return '<div class="dd-handle uk-box-shadow-hover-medium uk-position-relative ' . $state . '">'
			. IconSvg::render('move', 18, 18) . ' ' . $title
			. '<ul class="uk-iconnav uk-position-center-right uk-position-small uk-visible@s dd-nodrag">'
			. '<li><a class="uk-text-' . ($state === 'P' ? 'emphasis' : 'meta') . '" data-action="P" href="" uk-icon="icon: check"></a></li>'
			. '<li><a class="uk-text-' . ($state === 'U' ? 'emphasis' : 'meta') . '" data-action="U" href="" uk-icon="icon: close"></a></li>'
			. '<li><a class="uk-text-meta" data-action="T" href="" uk-icon="icon: trash"></a></li></ul></div>';
	}
}
