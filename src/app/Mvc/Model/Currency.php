<?php

namespace App\Mvc\Model;

use MaiVu\Php\Registry;

class Currency extends ModelBase
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
	 * @var double
	 */
	public $rate;

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
		$this->setSource('currencies');
	}

	public function getSearchFields()
	{
		return [
			'name',
			'code',
		];
	}

	public function getOrderFields()
	{
		return [
			'name',
			'rate',
			'code',
			'id',
		];
	}

	public function format($number) : string
	{
		$params    = $this->registry('params');
		$symbol    = $params->get('symbol', '$', 'string');
		$decimals  = $params->get('decimals', 2, 'uint');
		$point     = $params->get('point', '.', 'string');
		$separator = $params->get('separator', ',', 'string');
		$format    = $params->get('format', '{symbol}{value}', 'string');
		$value     = number_format((float) $number, $decimals, $point, $separator);

		return str_replace(['{symbol}', '{value}', '{code}'], [$symbol, $value, $this->code], $format);
	}
}