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
use function array_slice;
use function preg_match;
use function sprintf;

/**
 * Tests the \Jyxo\Beholder\TestCase\Imap class.
 *
 * @see \Jyxo\Beholder\TestCase\Imap
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class ImapTest extends DefaultTest
{

	/**
	 * Tests connection failure.
	 */
	public function testConnectionFailure(): void
	{
		$host = 'dummy.jyxo.com';

		$test = new Imap('Imap', $host);
		// @ on purpose
		$result = @$test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Connection error @%s:143', $host), $result->getDescription());
	}

	/**
	 * Tests working connection.
	 */
	public function testAllOk(): void
	{
		// Skip the test if no IMAP connection is defined
		if (empty($GLOBALS['imap']) || (!preg_match('~^([^:]+):([^@]+)@([^:]+):(\\d+)$~', $GLOBALS['imap'], $matches))) {
			$this->markTestSkipped('Imap not set');
		}

		[$user, $password, $host, $port] = array_slice($matches, 1);

		$test = new Imap('Imap', $host, $user, $password, $port, false);
		$result = $test->run();
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('%s@%s:%s', $user, $host, $port), $result->getDescription());
	}

}
