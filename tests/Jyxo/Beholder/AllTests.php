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

namespace Jyxo\Beholder;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Tests suite for \Jyxo\Beholder.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class AllTests
{
	/**
	 * Runs testing.
	 */
	public static function main()
	{
		\PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	/**
	 * Creates the test suite.
	 *
	 * @return \PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new \PHPUnit_Framework_TestSuite('Jyxo Beholder');

		$suite->addTestSuite('\Jyxo\Beholder\ResultTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCase\FileSystemTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCase\ImapTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCase\MemcachedTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCase\PgsqlTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCase\PhpExtensionTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCase\PhpVersionTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCase\PhpZendTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCase\SmtpTest');
		$suite->addTestSuite('\Jyxo\Beholder\TestCaseTest');

		return $suite;
	}
}
