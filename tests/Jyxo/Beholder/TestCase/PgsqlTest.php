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
use PHPUnit\Framework\TestCase;
use function class_exists;
use function preg_match;
use function sprintf;
use function time;

/**
 * Tests the \Jyxo\Beholder\TestCase\Pgsql class.
 *
 * @see \Jyxo\Beholder\TestCase\Pgsql
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class PgsqlTest extends TestCase
{

	/**
	 * Tests connection failure.
	 */
	public function testConnectionFailure(): void
	{
		$database = 'dummy';
		$host = 'dummy.jyxo.com';

		$test = new Pgsql('Pgsql', 'SELECT 1', $database, $host);
		// @ on purpose
		$result = @$test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Connection error @%s:5432/%s', $host, $database), $result->getDescription());
	}

	/**
	 * Tests query failure.
	 */
	public function testQueryFailure(): void
	{
		$pgsql = $this->getPgsql();

		$test = new Pgsql(
			'Pgsql',
			'SELECT * FROM test' . time(),
			$pgsql['database'],
			$pgsql['host'],
			$pgsql['user'],
			$pgsql['password'],
			$pgsql['port']
		);
		// @ on purpose
		$result = @$test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals(
			sprintf('Query error %s@%s:%s/%s', $pgsql['user'], $pgsql['host'], $pgsql['port'], $pgsql['database']),
			$result->getDescription()
		);
	}

	/**
	 * Tests everything working.
	 */
	public function testAllOk(): void
	{
		$pgsql = $this->getPgsql();

		$test = new Pgsql('Pgsql', 'SELECT 1', $pgsql['database'], $pgsql['host'], $pgsql['user'], $pgsql['password'], $pgsql['port']);
		$result = $test->run();
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals(
			sprintf('%s@%s:%s/%s', $pgsql['user'], $pgsql['host'], $pgsql['port'], $pgsql['database']),
			$result->getDescription()
		);
	}

	protected function setUp(): void
	{
		if (!class_exists('Pgsql')) {
			$this->markTestSkipped('Pgsql not set');
		}
	}

	/**
	 * Returns connection settings.
	 *
	 * @return array
	 */
	private function getPgsql(): array
	{
		// Skips the test if no PostgreSQL connection is defined
		if (empty($GLOBALS['pgsql']) || (!preg_match('~^([^:]+):([^@]+)@([^:]+):(\\d+)/(\\w+)$~', $GLOBALS['pgsql'], $matches))) {
			$this->markTestSkipped('PostgreSQL not set');
		}

		return [
			'user' => $matches[1],
			'password' => $matches[2],
			'host' => $matches[3],
			'port' => $matches[4],
			'database' => $matches[5],
		];
	}

}
