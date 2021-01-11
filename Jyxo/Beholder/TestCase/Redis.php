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

use Jyxo\Beholder\Result;
use Jyxo\Beholder\TestCase;
use Predis\Client;
use function class_exists;
use function extension_loaded;
use function filter_var;
use function gethostbyaddr;
use function md5;
use function sprintf;
use function time;
use function uniqid;
use const FILTER_VALIDATE_IP;

/**
 * Tests Redis availability.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Redis extends TestCase
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
	 * @var int
	 */
	private $port;

	/**
	 * Database index.
	 *
	 * @var int
	 */
	private $database;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $host Server host
	 * @param int $port Port
	 * @param int $database Database index
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
	 * @return Result
	 */
	public function run(): Result
	{
		// The redis extension or Predis library is required
		if (!extension_loaded('redis') && !class_exists(Client::class)) {
			return new Result(Result::NOT_APPLICABLE, 'Extension redis or Predis library required');
		}

		$random = md5(uniqid((string) time(), true));
		$key = 'beholder-' . $random;
		$value = $random;

		// Status label
		$description = (filter_var($this->host, FILTER_VALIDATE_IP) !== false ? gethostbyaddr(
			$this->host
		) : $this->host) . ':' . $this->port . '?database=' . $this->database;

		// Connection
		if (extension_loaded('redis')) {
			$redis = new \Redis();

			if ($redis->connect($this->host, $this->port, 2) === false) {
				return new Result(Result::FAILURE, sprintf('Connection error %s', $description));
			}
		} else {
			$redis = new Client(['host' => $this->host, 'port' => $this->port]);

			if ($redis->connect() === false) {
				return new Result(Result::FAILURE, sprintf('Connection error %s', $description));
			}
		}

		// Select database
		if ($redis->select($this->database) === false) {
			return new Result(Result::FAILURE, sprintf('Database error %s', $description));
		}

		// Saving
		if ($redis->set($key, $value) === false) {
			if ($redis instanceof \Redis) {
				$redis->close();
			} else {
				$redis->quit();
			}

			return new Result(Result::FAILURE, sprintf('Write error %s', $description));
		}

		// Check
		$check = $redis->get($key);

		if (($check === false) || ($check !== $value)) {
			if ($redis instanceof \Redis) {
				$redis->close();
			} else {
				$redis->quit();
			}

			return new Result(Result::FAILURE, sprintf('Read error %s', $description));
		}

		// Deleting
		if ($redis->del($key) === false) {
			if ($redis instanceof \Redis) {
				$redis->close();
			} else {
				$redis->quit();
			}

			return new Result(Result::FAILURE, sprintf('Delete error %s', $description));
		}

		// Disconnect
		if ($redis instanceof \Redis) {
			$redis->close();
		} else {
			$redis->quit();
		}

		// OK
		return new Result(Result::SUCCESS, $description);
	}

}
