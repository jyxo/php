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

namespace Jyxo\Mail;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Test suite for \Jyxo\Mail.
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
		$suite = new \PHPUnit_Framework_TestSuite('Jyxo Mail');

		$suite->addTestSuite('\Jyxo\Mail\Email\Attachment\FileTest');
		$suite->addTestSuite('\Jyxo\Mail\Email\Attachment\InlineFileTest');
		$suite->addTestSuite('\Jyxo\Mail\Email\Attachment\InlineStringTest');
		$suite->addTestSuite('\Jyxo\Mail\Email\Attachment\StringTest');
		$suite->addTestSuite('\Jyxo\Mail\Email\AddressTest');
		$suite->addTestSuite('\Jyxo\Mail\Email\BodyTest');
		$suite->addTestSuite('\Jyxo\Mail\Email\HeaderTest');
		$suite->addTestSuite('\Jyxo\Mail\EncodingTest');
		$suite->addTestSuite('\Jyxo\Mail\EmailTest');
		$suite->addTestSuite('\Jyxo\Mail\SenderTest');

		return $suite;
	}
}
