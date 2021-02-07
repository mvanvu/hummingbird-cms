<?php

namespace App\Mvc\Model;

use App\Helper\Date;
use App\Helper\Service;
use App\Helper\Text;
use App\Helper\User as Auth;
use Phalcon\Mvc\Model;

class Log extends Model
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
	public $stringKey;

	/**
	 *
	 * @var string | array
	 */
	public $payload = [];

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

	public static function addEntry($stringKey, array $stringData = [], $context = 'system')
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
			$userId    = Auth::getInstance()->id ?? 0;
		}

		$createdAt = Date::now('UTC')->toSql();
		$payload   = array_merge(['date' => $createdAt], $stringData);
		$entry     = new Log;
		$created   = $entry->assign(
			[
				'context'   => $context,
				'stringKey' => $stringKey,
				'payload'   => json_encode($payload),
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

	public function getSummary()
	{
		return static::parseSummary($this->stringKey, $this->payload);
	}

	/**
	 * @param $stringKey
	 * @param $payload
	 *
	 * @return string
	 */

	public static function parseSummary($stringKey, $payload)
	{
		if (is_string($payload))
		{
			$payload = @json_decode($payload, true) ?: [];
		}

		if (empty($payload) && !is_array($payload))
		{
			$payload = [];
		}

		if (isset($payload['date']))
		{
			$payload['date'] = Date::relative($payload['date']);
		}

		return call_user_func_array(Text::class . '::_', [$stringKey, $payload]);
	}

	public function getTitle()
	{
		return Text::_($this->context . '-logs-title');
	}

	public function __toString()
	{
		return static::parseSummary($this->stringKey, $this->payload);
	}
}