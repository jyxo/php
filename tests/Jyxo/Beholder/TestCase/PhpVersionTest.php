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
 * Tests the \Jyxo\Beholder\TestCase\PhpVersion class.
 *
 * @see \Jyxo\Beholder\TestCase\PhpVersion
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class PhpVersionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests PHP version not matching.
	 */
	public function testPhpVersionWrong()
	{
		$test = new PhpVersion('Version', '5.2');
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Version %s, expected = %s', phpversion(), '5.2'), $result->getDescription());
	}

	/**
	 * Tests PHP version being greater than required.
	 */
	public function testPhpVersionGreaterThan()
	{
		$test = new PhpVersion('Version', '5.2', '', '>=');
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('Version %s', phpversion()), $result->getDescription());
	}

	/**
	 * Tests PHP version being equal to required.
	 */
	public function testPhpVersionEquals()
	{
		$test = new PhpVersion('Version', phpversion());
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('Version %s', phpversion()), $result->getDescription());
	}

	/**
	 * Tests PHP version being wrong.
	 */
	public function testExtensionVersionWrong()
	{
		$test = new PhpVersion('Version', '3.0', 'core');
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Version %s, expected = %s', phpversion('core'), '3.0'), $result->getDescription());
	}

	/**
	 * Tests extension version being greater than required.
	 */
	public function testExtensionVersionGreaterThan()
	{
		$test = new PhpVersion('Version', '3.0', 'core', '>=');
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('Version %s', phpversion('core')), $result->getDescription());
	}

	/**
	 * Tests extension version being equal to required.
	 */
	public function testExtensionVersionEquals()
	{
		$test = new PhpVersion('Version', phpversion('core'), 'core');
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('Version %s', phpversion('core')), $result->getDescription());
	}

	/**
	 * Tests missing extensions.
	 */
	public function testExtensionMissing()
	{
		$test = new PhpVersion('Version', '1.0', 'runkit');
		$result = $test->run();
		$this->assertEquals(\Jyxo\Beholder\Result::NOT_APPLICABLE, $result->getStatus());
		$this->assertEquals('Extension runkit missing', $result->getDescription());
	}
}
