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
use function phpversion;
use function sprintf;

/**
 * Tests the \Jyxo\Beholder\TestCase\PhpVersion class.
 *
 * @see \Jyxo\Beholder\TestCase\PhpVersion
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class PhpVersionTest extends TestCase
{

	/**
	 * Tests PHP version not matching.
	 */
	public function testPhpVersionWrong(): void
	{
		$test = new PhpVersion('Version', '5.2');
		$result = $test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Version %s, expected = %s', phpversion(), '5.2'), $result->getDescription());
	}

	/**
	 * Tests PHP version being greater than required.
	 */
	public function testPhpVersionGreaterThan(): void
	{
		$test = new PhpVersion('Version', '5.2', '', '>=');
		$result = $test->run();
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('Version %s', phpversion()), $result->getDescription());
	}

	/**
	 * Tests PHP version being equal to required.
	 */
	public function testPhpVersionEquals(): void
	{
		$test = new PhpVersion('Version', phpversion());
		$result = $test->run();
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('Version %s', phpversion()), $result->getDescription());
	}

	/**
	 * Tests PHP version being wrong.
	 */
	public function testExtensionVersionWrong(): void
	{
		$test = new PhpVersion('Version', '3.0', 'core');
		$result = $test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals(sprintf('Version %s, expected = %s', phpversion('core'), '3.0'), $result->getDescription());
	}

	/**
	 * Tests extension version being greater than required.
	 */
	public function testExtensionVersionGreaterThan(): void
	{
		$test = new PhpVersion('Version', '3.0', 'core', '>=');
		$result = $test->run();
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('Version %s', phpversion('core')), $result->getDescription());
	}

	/**
	 * Tests extension version being equal to required.
	 */
	public function testExtensionVersionEquals(): void
	{
		$test = new PhpVersion('Version', phpversion('core'), 'core');
		$result = $test->run();
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals(sprintf('Version %s', phpversion('core')), $result->getDescription());
	}

	/**
	 * Tests missing extensions.
	 */
	public function testExtensionMissing(): void
	{
		$test = new PhpVersion('Version', '1.0', 'runkit');
		$result = $test->run();
		$this->assertEquals(Result::NOT_APPLICABLE, $result->getStatus());
		$this->assertEquals('Extension runkit missing', $result->getDescription());
	}

}
