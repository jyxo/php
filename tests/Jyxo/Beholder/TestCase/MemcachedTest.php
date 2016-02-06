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
 * Tests the \Jyxo\Beholder\TestCase\Memcached class.
 *
 * @see \Jyxo\Beholder\TestCase\Memcached
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class MemcachedTest extends \PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		if (!class_exists('Memcached')) {
			$this->markTestSkipped('Memcached not set');
		}
	}

	/**
	 * Tests connection failure.
	 */
	public function testConnectionFailure()
	{
		if (!class_exists('Memcached')) {
			$this->markTestSkipped('Memcached not set');
		}

		$ip = '127.0.0.1';
		$port = '12345';

		$test = new Memcached('Memcached', $ip, $port);
		// @ on purpose
		$result = @$test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Connection error %s:%s', gethostbyaddr($ip), $port), $result->getDescription());
	}

	/**
	 * Tests working connection.
	 */
	public function testAllOk()
	{
		// Skip the test if no memcache connection is defined
		if (empty($GLOBALS['memcache'])) {
			$this->markTestSkipped('Memcached not set');
		}

		$servers = explode(',', $GLOBALS['memcache']);
		list($ip, $port) = explode(':', $servers[0]);

		$test = new Memcached('Memcached', $ip, $port);
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('%s:%s', gethostbyaddr($ip), $port), $result->getDescription());
	}
}
