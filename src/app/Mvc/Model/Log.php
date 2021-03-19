<?php

namespace App\Mvc\Model;

use App\Helper\Date;
use App\Helper\Service;
use App\Helper\User as Auth;

class Log extends ModelBase
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
	public $context;

	/**
	 *
	 * @var string
	 */
	public $message;


	/**
	 *
	 * @var string
	 */
	public $createdAt;

	/**
	 *
	 * @var string
	 */
	public $ip;

	/**
	 *
	 * @var string
	 */
	public $userAgent;

	public static function addEntry(string $message, string $context = 'system')
	{
		if (IS_CLI)
		{
			$ip        = '127.0.0.1';
			$userAgent = 'Hb/Cli';
			$userId    = 0;
		}
		else
		{
			$request   = Service::request();
			$ip        = $request->getClientAddress() ?? 'N/A';
			$userAgent = $request->getUserAgent() ?? '';
			$userId    = Auth::id() ?? 0;
		}

		$createdAt = Date::now('UTC')->toSql();
		$entry     = new Log;
		$created   = $entry->assign(
			[
				'id'        => md5($context . ':' . $createdAt . ':' . (Log::count() ?? 0 + 1)),
				'context'   => $context,
				'message'   => $message,
				'userId'    => $userId,
				'ip'        => $ip,
				'userAgent' => $userAgent,
				'createdAt' => $createdAt,
			]
		)->create();

		return $created ? $entry : false;
	}

	public static function getByContext($context)
	{
		if (!is_array($context))
		{
			$context = [$context];
		}

		$context = array_map(function ($ctx) {
			return '\'' . $ctx . '\'';
		}, $context);

		return parent::find(
			[
				'conditions' => 'context IN ({context:array})',
				'bind'       => [
					'context' => $context,
				],
				'order'      => 'id DESC'
			]
		);
	}

	public function initialize()
	{
		$this->setSource('logs');
		$this->belongsTo('userId', User::class, 'id', ['reusable' => true, 'alias' => 'user']);
	}

	public function getSearchFields()
	{
		return [
			'context',
			'message',
			'ip',
		];
	}

	public function getOrderFields()
	{
		return [
			'createdAt',
			'message',
			'ip',
			'userId',
		];
	}

	public function __toString()
	{
		return $this->message;
	}
}