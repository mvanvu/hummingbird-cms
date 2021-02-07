<?php

namespace App\Helper;

use App\Factory\Factory;
use Phalcon\Paginator\RepositoryInterface;

class Nested
{
	/** @var Uri */
	protected $uri;

	/** @var array */
	protected $sources;

	/** @var integer */
	protected $count = 0;

	public function __construct(Uri $uri = null, RepositoryInterface $sources = null)
	{
		$this->uri = $uri;
		$this->setSources($sources);
		Assets::jQueryCore();
	}

	public function setSources(RepositoryInterface $sources = null)
	{
		if (null === $sources)
		{
			$this->count = 0;
		}
		else
		{
			$this->count = $sources->getTotalItems();
		}

		$this->sources = [
			'1' => [],
			'p' => [],
		];

		foreach ($sources->getItems() as $source)
		{
			if ('1' == $source->level)
			{
				$this->sources['1'][] = $source;
			}
			else
			{
				$this->sources['p'][$source->parentId][] = $source;
			}
		}

		return $this;
	}

	public function makeTree(array $items = null)
	{
		$tree = '<ol class="dd-list">';

		if (null === $items)
		{
			$items = $this->sources['1'];
		}

		foreach ($items as $item)
		{
			$hasChildren = isset($this->sources['p'][$item->id]);
			$title       = htmlspecialchars($item->title);
			$tree        .= '<li class="dd-item ' . ($hasChildren ? 'has-children' : 'no-children') . '" data-id="' . $item->id . '" data-title="' . $title . '">'
				. $this->makeHandle($item);

			if ($hasChildren)
			{
				$tree .= $this->makeTree($this->sources['p'][$item->id]);
			}

			$tree .= '</li>';
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
