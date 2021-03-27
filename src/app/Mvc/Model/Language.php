<?php

namespace App\Mvc\Model;

use App\Helper\FileSystem;
use App\Helper\Service;
use App\Helper\Text;
use MaiVu\Php\Registry;

class Language extends ModelBase
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
	public $state;

	/**
	 *
	 * @var string
	 */
	public $code;

	/**
	 *
	 * @var string
	 */
	public $sef;

	/**
	 *
	 * @var string
	 */
	public $iso;

	/**
	 *
	 * @var string
	 */
	public $protected = 'N';

	/**
	 *
	 * @var string
	 */
	public $direction;

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
	 * @var array|string|Registry
	 */
	public $params = '{}';

	/**
	 *
	 * @var string
	 */
	protected $titleField = 'name';

	/**
	 * @var string[]
	 */
	protected $jsonFields = ['params'];

	/**
	 * @var bool
	 */

	protected $standardMetadata = true;

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('languages');
		$this->skipAttributes(['protected']);
	}

	public function getSearchFields()
	{
		return [
			'name',
			'code',
			'iso',
		];
	}

	public function getOrderFields()
	{
		return [
			'createdAt',
			'name',
			'code',
			'iso',
		];
	}

	public function beforeSave()
	{
		if ($this->yes('protected') && $this->state !== 'P')
		{
			Service::flashSession()->warning(Text::_('lang-def-restrict-msg'));

			return false;
		}
	}

	public function afterSave()
	{
		$langFile = APP_PATH . '/Language/' . $this->code . '.php';

		if (!is_file($langFile))
		{
			FileSystem::copy(APP_PATH . '/Language/en-GB.php', $langFile, true);
		}
	}

	public function afterDelete()
	{
		FileSystem::remove(APP_PATH . '/Language/' . $this->code . '.php');
	}
}