<?php

namespace App\Form\Field;

use App\Mvc\Model\Tag;
use MaiVu\Php\Form\Field\Select;

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
