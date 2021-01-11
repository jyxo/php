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
use Memcache;
use function extension_loaded;
use function filter_var;
use function gethostbyaddr;
use function md5;
use function sprintf;
use function time;
use function uniqid;
use const FILTER_VALIDATE_IP;

/**
 * Tests memcached availability.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Memcached extends TestCase
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
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $host Server host
	 * @param int $port Port
	 */
	public function __construct(string $description, string $host, int $port)
	{
		parent::__construct($description);

		$this->host = $host;
		$this->port = $port;
	}

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	public function run(): Result
	{
		// The memcached or memcache extension is required
		if (!extension_loaded('memcached') && !extension_loaded('memcache')) {
			return new Result(Result::NOT_APPLICABLE, 'Extension memcached or memcache required');
		}

		$random = md5(uniqid((string) time(), true));
		$key = 'beholder-' . $random;
		$value = $random;

		// Status label
		$description = (filter_var($this->host, FILTER_VALIDATE_IP) !== false ? gethostbyaddr(
			$this->host
		) : $this->host) . ':' . $this->port;

		if (extension_loaded('memcached')) {
			// Connection
			$memcached = new \Memcached();

			if ($memcached->addServer($this->host, $this->port) === false) {
				return new Result(Result::FAILURE, sprintf('Connection error %s', $description));
			}
		} else {
			// Connection (@ due to notice)
			$memcached = new Memcache();

			if (@$memcached->connect($this->host, $this->port) === false) {
				return new Result(Result::FAILURE, sprintf('Connection error %s', $description));
			}
		}

		// Saving
		if ($memcached->set($key, $value) === false) {
			if ($memcached instanceof \Memcached) {
				$memcached->quit();
			} else {
				$memcached->close();
			}

			return new Result(Result::FAILURE, sprintf('Write error %s', $description));
		}

		// Check
		$check = $memcached->get($key);

		if (($check === false) || ($check !== $value)) {
			if ($memcached instanceof \Memcached) {
				$memcached->quit();
			} else {
				$memcached->close();
			}

			return new Result(Result::FAILURE, sprintf('Read error %s', $description));
		}

		// Deleting
		if ($memcached->delete($key) === false) {
			if ($memcached instanceof \Memcached) {
				$memcached->quit();
			} else {
				$memcached->close();
			}

			return new Result(Result::FAILURE, sprintf('Delete error %s', $description));
		}

		// Disconnect
		if ($memcached instanceof \Memcached) {
			$memcached->quit();
		} else {
			$memcached->close();
		}

		// OK
		return new Result(Result::SUCCESS, $description);
	}

}
