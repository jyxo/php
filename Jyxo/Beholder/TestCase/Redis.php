<?php declare(strict_types = 1);

/**
 * Jyxo PHP Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Beholder\TestCase;

/**
 * Tests Redis availability.
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Redis extends \Jyxo\Beholder\TestCase
{
	/**
	 * Server host.
	 *
	 * @var string
	 */
	private $host;

	/**
	 * Port.
	 *
	 * @var integer
	 */
	private $port;

	/**
	 * Database index.
	 *
	 * @var integer
	 */
	private $database;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $host Server host
	 * @param integer $port Port
	 * @param integer $database Database index
	 */
	public function __construct(string $description, string $host, int $port = 6379, int $database = 0)
	{
		parent::__construct($description);

		$this->host = $host;
		$this->port = $port;
		$this->database = $database;
	}

	/**
	 * Performs the test.
	 *
	 * @return \Jyxo\Beholder\Result
	 */
	public function run(): \Jyxo\Beholder\Result
	{
		// The redis extension or Predis library is required
		if (!extension_loaded('redis') && !class_exists(\Predis\Client::class)) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Extension redis or Predis library required');
		}

		$random = md5(uniqid((string) time(), true));
		$key = 'beholder-' . $random;
		$value = $random;

		// Status label
		$description = (false !== filter_var($this->host, FILTER_VALIDATE_IP) ? gethostbyaddr($this->host) : $this->host) . ':' . $this->port . '?database=' . $this->database;

		// Connection
		if (extension_loaded('redis')) {
			$redis = new \Redis();
			if (false === $redis->connect($this->host, $this->port, 2)) {
				return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Connection error %s', $description));
			}
		} else {
			$redis = new \Predis\Client(['host' => $this->host, 'port' => $this->port]);
			if (false === $redis->connect()) {
				return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Connection error %s', $description));
			}
		}

		// Select database
		if (false === $redis->select($this->database)) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Database error %s', $description));
		}

		// Saving
		if (false === $redis->set($key, $value)) {
			if ($redis instanceof \Redis) {
				$redis->close();
			} else {
				$redis->quit();
			}
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Write error %s', $description));
		}

		// Check
		$check = $redis->get($key);
		if ((false === $check) || ($check !== $value)) {
			if ($redis instanceof \Redis) {
				$redis->close();
			} else {
				$redis->quit();
			}
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Read error %s', $description));
		}

		// Deleting
		if (false === $redis->del($key)) {
			if ($redis instanceof \Redis) {
				$redis->close();
			} else {
				$redis->quit();
			}
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Delete error %s', $description));
		}

		// Disconnect
		if ($redis instanceof \Redis) {
			$redis->close();
		} else {
			$redis->quit();
		}

		// OK
		return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::SUCCESS, $description);
	}
}
