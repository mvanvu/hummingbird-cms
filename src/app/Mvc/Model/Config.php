<?php

namespace App\Mvc\Model;

use App\Helper\Config as ConfigHelper;
use App\Helper\Service;
use MaiVu\Php\Form\Form;
use MaiVu\Php\Form\FormsManager;

class Config extends ModelBase
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
	public $context;

	/**
	 *
	 * @var string
	 */
	public $data;

	/**
	 *
	 * @var integer
	 */
	public $ordering;

	/**
	 * @var array
	 */
	protected $jsonFields = ['data'];

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource('config_data');
	}

	public function getFormsManager()
	{
		return new FormsManager(
			[
				'site'    => Form::create(__DIR__ . '/Form/Config/Site.php'),
				'locale'  => Form::create(__DIR__ . '/Form/Config/Locale.php'),
				'user'    => Form::create(__DIR__ . '/Form/Config/User.php'),
				'comment' => Form::create(__DIR__ . '/Form/Config/Comment.php'),
				'system'  => Form::create(__DIR__ . '/Form/Config/System.php'),
			]
		);
	}

	public function afterDelete()
	{
		$prefix = $this->getModelsManager()->getModelPrefix();
		$db     = Service::db();
		$db->execute('DELETE FROM ' . $prefix . 'translations WHERE translationId LIKE :translationId',
			[
				'translationId' => '%.config_data.id=' . $this->id,
			]
		);
	}
}
