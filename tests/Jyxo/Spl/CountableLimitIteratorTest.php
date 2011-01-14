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

namespace Jyxo\Spl;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Test for class \Jyxo\Spl\CountableLimitIterator.
 *
 * @see \Jyxo\Spl\Object
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 * @author Ondřej Nešpor
 */
class CountableLimitIteratorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests use of limit.
	 */
	public function testLimit()
	{
		$data = range(0, 10);
		$expected = range(2, 4);

		$iterator = new CountableLimitIterator(new \ArrayIterator($data), 2, 3);
		$result = iterator_to_array($iterator);

		$this->assertEquals(array_values($expected), array_values($result));
	}

	/**
	 * Tests return count.
	 */
	public function testPassCount()
	{
		$data = range(0, 10);
		$expected = range(2, 4);

		$iterator = new CountableLimitIterator(new \ArrayIterator($data), 2, 3);
		$this->assertEquals(count($data), count($iterator));
	}

	/**
	 * Tests return count - real value.
	 */
	public function testLimitCount()
	{
		$data = range(0, 10);
		$expected = range(2, 4);

		$iterator = new CountableLimitIterator(new \ArrayIterator($data), 2, 3, CountableLimitIterator::MODE_LIMIT);

		$this->assertEquals(3, count($iterator));
		$result = iterator_to_array($iterator);
		$this->assertEquals(3, count($result));
	}

	/**
	 * Tests the count() method when the limit is out of the inner \Iterator.
	 *
	 */
	public function testOutOfBounds()
	{
		$data = range(1, 2);

		$iterator = new CountableLimitIterator(new \ArrayIterator($data), 5, 2, CountableLimitIterator::MODE_LIMIT);

		$this->assertSame(0, count($iterator));
	}

	/**
	 * Tests creating an instance with an invalid \Iterator.
	 */
	public function testInvalidIterator()
	{
		$this->setExpectedException('\InvalidArgumentException');
		$iterator = new CountableLimitIterator(new \EmptyIterator());
	}
}
