<?php

namespace App\Form\Field;

use App\Factory\Factory;
use App\Helper\Service;
use MaiVu\Php\Form\Field\Select;
use Phalcon\Db\Enum;

class CmsSQL extends Select
{
	protected $query;

	public function getOptions()
	{
		$options = parent::getOptions();
		$query   = str_replace('#__', Factory::getConfig()->get('db.prefix'), $this->query);

		if ($rows = Service::db()->fetchAll($query, Enum::FETCH_ASSOC))
		{
			foreach ($rows as $row)
			{
				$options[] = [
					'value' => $row['value'],
					'text'  => $row['text'],
				];
			}
		}

		return $options;
	}
}
