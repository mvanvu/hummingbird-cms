<?php


namespace App\Mvc\Model;

use App\Helper\Queue;
use Phalcon\Mvc\Model;

class QueueJob extends Model
{
	/**
	 * @var string
	 */

	public $queueJobId;

	/**
	 * @var string
	 */

	public $handler;

	/**
	 * @var string
	 */

	public $payload;

	/**
	 *
	 * @var string
	 */
	public $createdAt;

	/**
	 *
	 * @var string
	 */
	public $failedAt;

	/**
	 *
	 * @var integer
	 */
	public $createdBy = 0;

	/**
	 *
	 * @var integer
	 */
	public $priority = Queue::PRIORITY_NORMAL;


	public function initialize()
	{
		$this->setSource('queue_jobs');
	}
}