<?php

namespace App\Mvc\Model;

class SocketData extends ModelBase
{
	/**
	 *
	 * @var integer
	 */
	public $id;

	/**
	 *
	 * @var integer
	 */
	public $userId;

	/**
	 *
	 * @var string
	 */
	public $context;

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


	public function initialize()
	{
		$this->setSource('socket_data');
		$this->hasOne('createdBy', User::class, 'id', ['reusable' => true, 'alias' => 'user']);
	}
}
