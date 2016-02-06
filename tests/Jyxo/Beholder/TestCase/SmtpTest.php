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
 * Tests the \Jyxo\Beholder\TestCase\Smtp class.
 *
 * @see \Jyxo\Beholder\TestCase\Smtp
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class SmtpTest extends \Jyxo\Beholder\TestCase\DefaultTest
{
	/**
	 * Tests for the sender class missing.
	 */
	public function testSmtpMissing()
	{
		// Skips the test if the class is already loaded
		if (class_exists(\Jyxo\Mail\Sender\Smtp::class, false)) {
			$this->markTestSkipped(sprintf('%s already loaded', \Jyxo\Mail\Sender\Smtp::class));
		}

		$test = new Smtp('Smtp', '', '', '');

		// Turns autoload off
		$this->disableAutoload();

		$result = $test->run();

		// Turns autoload on
		$this->enableAutoload();

		$this->assertEquals(\Jyxo\Beholder\Result::NOT_APPLICABLE, $result->getStatus());
		$this->assertEquals(sprintf('Class %s missing', \Jyxo\Mail\Sender\Smtp::class), $result->getDescription());
	}

	/**
	 * Tests for a sending failure.
	 */
	public function testSendFailure()
	{
		$test = new Smtp('Smtp', 'dummy.jyxo.com', '', '');
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::FAILURE, $result->getStatus());
		$this->assertEquals('Send error dummy.jyxo.com', $result->getDescription());
	}

	/**
	 * Tests for a successful sending.
	 */
	public function testSendOk()
	{
		// Skips the test if no SMTP connection is defined
		if (empty($GLOBALS['smtp'])) {
			$this->markTestSkipped('Smtp host not set');
		}

		$test = new Smtp('Smtp', $GLOBALS['smtp'], 'blog-noreply@blog.cz');
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::SUCCESS, $result->getStatus());
		$this->assertEquals($GLOBALS['smtp'], $result->getDescription());
	}
}
