<?php

namespace App\Mvc\Model;

use Phalcon\Mvc\Model;

class SocketData extends Model
{
	/**
	 *
	 * @var string
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
