<?php

namespace MaiVu\Hummingbird\Lib\Form\Field;

use MaiVu\Hummingbird\Lib\Mvc\Model\Tag;

class CmsTag extends Select
{
	public function getOptions()
	{
		static $tags = null;

		if (null === $tags)
		{
			$tags  = [];
			$items = Tag::find();

			if ($items->count())
			{
				foreach ($items as $item)
				{
					$tags[$item->id] = $item->title;
				}
			}
		}

		return $tags;
	}
}
