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

namespace Jyxo\Beholder;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Test for the \Jyxo\Beholder\Result class.
 *
 * @see \Jyxo\Beholder\Result
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class ResultTest extends TestCase
{

	/**
	 * Tests success results.
	 */
	public function testSuccess(): void
	{
		// No label
		$result = new Result(Result::SUCCESS);
		$this->assertTrue($result->isSuccess());
		$this->assertEquals(Result::SUCCESS, $result->getStatus());
		$this->assertEquals($result->getStatusMessage(), $result->getDescription());

		// With label
		$result = new Result(Result::SUCCESS, 'Desc');
		$this->assertEquals('Desc', $result->getDescription());
	}

	/**
	 * Tests failure results.
	 */
	public function testFailure(): void
	{
		// No label
		$result = new Result(Result::FAILURE);
		$this->assertTrue(!$result->isSuccess());
		$this->assertEquals(Result::FAILURE, $result->getStatus());
		$this->assertEquals($result->getStatusMessage(), $result->getDescription());

		// With label
		$result = new Result(Result::FAILURE, 'Desc');
		$this->assertEquals('Desc', $result->getDescription());
	}

	/**
	 * Tests not applicable results.
	 */
	public function testNotApplicable(): void
	{
		// No label
		$result = new Result(Result::NOT_APPLICABLE);
		$this->assertTrue($result->isSuccess());
		$this->assertEquals(Result::NOT_APPLICABLE, $result->getStatus());
		$this->assertEquals($result->getStatusMessage(), $result->getDescription());

		// With label
		$result = new Result(Result::NOT_APPLICABLE, 'Desc');
		$this->assertEquals('Desc', $result->getDescription());
	}

	/**
	 * Tests the exception thrown on invalid result type.
	 */
	public function testInvalidStatus(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$result = new Result('dummy');
	}

}
