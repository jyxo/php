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

/**
 * Tests the \Jyxo\Beholder\TestCase\PhpExtension class.
 *
 * @see \Jyxo\Beholder\TestCase\PhpExtension
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class PhpExtensionTest extends TestCase
{

	/**
	 * Tests some extensions missing.
	 */
	public function testMissing(): void
	{
		$test = new PhpExtension('Extensions', ['pcre', 'runkit', 'parsekit']);
		$result = $test->run();
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals('Missing runkit, parsekit', $result->getDescription());
	}

	/**
	 * Tests all requested extension present.
	 */
	public function testAvailable(): void
	{
		$test = new PhpExtension('Extensions', ['pcre', 'spl', 'reflection']);
		$result = $test->run();
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals('OK', $result->getDescription());
	}

}
