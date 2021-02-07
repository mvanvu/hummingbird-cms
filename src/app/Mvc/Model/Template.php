<?php

namespace App\Mvc\Model;

use App\Helper\FileSystem;
use App\Helper\Service;
use App\Helper\Text;
use MaiVu\Php\Registry;
use Throwable;

class Template extends ModelBase
{
	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var string
	 */
	public $name;

	/**
	 *
	 * @var string
	 */
	public $createdAt;

	/**
	 *
	 * @var integer
	 */
	public $createdBy = 0;

	/**
	 *
	 * @var string
	 */
	public $modifiedAt = null;

	/**
	 *
	 * @var integer
	 */
	public $modifiedBy = 0;

	/**
	 *
	 * @var string
	 */
	public $checkedAt = null;

	/**
	 *
	 * @var integer
	 */
	public $checkedBy = 0;

	/**
	 *
	 * @var string
	 */
	public $isDefault = 'N';

	/**
	 *
	 * @var string
	 */
	public $params = '{}';

	/**
	 *
	 * @var string
	 */
	protected $titleField = 'name';

	/**
	 * @var boolean
	 */

	protected $standardMetadata = true;

	/**
	 * @var string[]
	 */

	protected $jsonFields = ['params'];

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('templates');
	}

	public function getOrderFields()
	{
		return [
			'id',
			'name',
			'createdAt',
		];
	}

	public function getSearchFields()
	{
		return ['name'];
	}

	public function getParams()
	{
		return new Registry($this->params);
	}

	public function beforeDelete()
	{
		if ($this->isDefault === 'Y')
		{
			Service::flashSession()->warning(Text::_('delete-default-tmpl-msg'));

			return false;
		}
	}

	public function copy()
	{
		if ($result = parent::copy())
		{
			$tplPath = APP_PATH . '/Tmpl/Site/Template-' . $this->id;

			if (is_dir($tplPath))
			{
				try
				{
					FileSystem::copy($tplPath, APP_PATH . '/Tmpl/Site/Template-' . $result->id, true);
				}
				catch (Throwable $e)
				{

				}
			}
		}

		return $result;
	}

	public function afterDelete()
	{
		$tplPath = APP_PATH . '/Tmpl/Site/Template-' . $this->id;

		if (is_dir($tplPath))
		{
			FileSystem::remove($tplPath);
		}
	}
}