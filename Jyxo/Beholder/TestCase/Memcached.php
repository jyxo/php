<?php

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
 * Tests memcache availability.
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
	 * Server ip address.
	 *
	 * @var string
	 */
	private $ip;

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
	 * @param string $ip Server address
	 * @param integer $port Port
	 */
	public function __construct($description, $ip, $port)
	{
		parent::__construct($description);

		$this->ip = (string) $ip;
		$this->port = (int) $port;
	}

	/**
	 * Performs the test.
	 *
	 * @return \Jyxo\Beholder\Result
	 */
	public function run()
	{
		// The memcache extension is required
		if (!extension_loaded('memcache')) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::NOT_APPLICABLE, 'Extension memcache missing');
		}

		$random = md5(uniqid(time(), true));
		$key = 'beholder-' . $random;
		$value = $random;

		// Status label
		$description = gethostbyaddr($this->ip) . ':' . $this->port;

		// Connection (@ due to notice)
		$memcache = new \Memcache();
		if (false === @$memcache->connect($this->ip, $this->port)) {
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Connection error %s', $description));
		}

		// Saving
		if (false === $memcache->set($key, $value)) {
			$memcache->close();
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Write error %s', $description));
		}

		// Check
		$check = $memcache->get($key);
		if ((false === $check) || ($check !== $value)) {
			$memcache->close();
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Read error %s', $description));
		}

		// Deleting
		if (false === $memcache->delete($key)) {
			$memcache->close();
			return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::FAILURE, sprintf('Delete error %s', $description));
		}

		$memcache->close();

		// OK
		return new \Jyxo\Beholder\Result(\Jyxo\Beholder\Result::SUCCESS, $description);
	}
}
