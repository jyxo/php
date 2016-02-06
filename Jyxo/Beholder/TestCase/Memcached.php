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
 * Tests memcached availability.
 *
 * @category Jyxo
 * @package Jyxo\Beholder
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Memcached extends \Jyxo\Beholder\TestCase
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
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $host Server host
	 * @param integer $port Port
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
	 * @return \Jyxo\Beholder\Result
	 */
	public function run(): \Jyxo\Beholder\Result
	{
		// The memcached or memcache extension is required
		if (!extension_loaded('memcached') && !extension_loaded('memcache')) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Extension memcached or memcache required');
		}

		$random = md5(uniqid((string) time(), true));
		$key = 'beholder-' . $random;
		$value = $random;

		// Status label
		$description = (false !== filter_var($this->host, FILTER_VALIDATE_IP) ? gethostbyaddr($this->host) : $this->host) . ':' . $this->port;

		if (extension_loaded('memcached')) {
			// Connection
			$memcached = new \Memcached();
			if (false === $memcached->addServer($this->host, $this->port)) {
				return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Connection error %s', $description));
			}
		} else {
			// Connection (@ due to notice)
			$memcached = new \Memcache();
			if (false === @$memcached->connect($this->host, $this->port)) {
				return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Connection error %s', $description));
			}
		}

		// Saving
		if (false === $memcached->set($key, $value)) {
			if ($memcached instanceof \Memcached) {
				$memcached->quit();
			} else {
				$memcached->close();
			}
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Write error %s', $description));
		}

		// Check
		$check = $memcached->get($key);
		if ((false === $check) || ($check !== $value)) {
			if ($memcached instanceof \Memcached) {
				$memcached->quit();
			} else {
				$memcached->close();
			}
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Read error %s', $description));
		}

		// Deleting
		if (false === $memcached->delete($key)) {
			if ($memcached instanceof \Memcached) {
				$memcached->quit();
			} else {
				$memcached->close();
			}
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Delete error %s', $description));
		}

		// Disconnect
		if ($memcached instanceof \Memcached) {
			$memcached->quit();
		} else {
			$memcached->close();
		}

		// OK
		return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::SUCCESS, $description);
	}
}
