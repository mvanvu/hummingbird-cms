<?php

namespace App\Service;

use App\Helper\Database;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Session\Adapter\AbstractAdapter;

class Session extends AbstractAdapter
{
	/**
	 * @var Mysql
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var bool
	 */
	protected $started = false;

	protected function __construct(Mysql $db)
	{
		$this->db    = $db;
		$this->table = Database::table('sessions');

		if (!headers_sent())
		{
			session_name('HB_SESSION_ID');
			session_set_save_handler(
				[$this, 'open'],
				[$this, 'close'],
				[$this, 'read'],
				[$this, 'write'],
				[$this, 'destroy'],
				[$this, 'gc']
			);
		}
	}

	public static function getInstance(Mysql $db)
	{
		static $instance = null;

		if (null === $instance)
		{
			$instance = new Session($db);
		}

		return $instance;
	}

	/**
	 * @param mixed $savePath
	 * @param mixed $sessionName
	 *
	 * @return bool
	 */
	public function open($savePath, $sessionName): bool
	{
		$this->started = true;

		return true;
	}

	/**
	 * @return bool
	 */
	public function close(): bool
	{
		$this->started = false;

		return true;
	}

	/**
	 * @param string $sessionId
	 *
	 * @return string
	 */
	public function read($sessionId): string
	{
		if (!$this->started)
		{
			return '';
		}

		$data = $this->db->fetchColumn(
			sprintf(
				'SELECT %s FROM %s WHERE %s = ?',
				$this->db->escapeIdentifier('data'),
				$this->db->escapeIdentifier($this->table),
				$this->db->escapeIdentifier('id')
			),
			[$sessionId]
		);

		return $data ?? '';
	}

	/**
	 * @param string $sessionId
	 * @param string $data
	 *
	 * @return bool
	 */
	public function write($sessionId, $data): bool
	{
		if (!$this->started)
		{
			return false;
		}

		$time = time();

		return $this->db->execute(
			sprintf(
				'INSERT INTO %s (%s,%s,%s) VALUES(?,?,?) ON DUPLICATE KEY UPDATE %s = ?, %s = ?',
				$this->db->escapeIdentifier($this->table),
				$this->db->escapeIdentifier('id'),
				$this->db->escapeIdentifier('data'),
				$this->db->escapeIdentifier('time'),
				$this->db->escapeIdentifier('data'),
				$this->db->escapeIdentifier('time')
			),
			[
				$sessionId,
				$data,
				$time,
				$data,
				$time,
			]
		);
	}

	/**
	 * @param string $sessionId
	 *
	 * @return bool
	 */
	public function destroy($sessionId): bool
	{
		if ($this->started)
		{
			$this->started = false;
			$this->db->delete($this->table, 'id = ?', [$sessionId]);
		}

		return true;
	}

	/**
	 * @param mixed $maxLifeTime
	 *
	 * @return bool
	 */
	public function gc($maxLifeTime): bool
	{
		return $this->db->delete($this->table, 'time < ?', [time() - (int) $maxLifeTime]);
	}
}