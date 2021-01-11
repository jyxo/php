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

namespace Jyxo\Spl;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use function range;

/**
 * Test for class \Jyxo\Spl\FilterIterator.
 *
 * @see \Jyxo\Spl\FilterIterator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Nešpor
 */
class FilterIteratorTest extends TestCase
{

	/**
	 * Tests the whole class.
	 */
	public function testIterator(): void
	{
		$data = range(0, 10);
		$callback = static function ($value) {
			return $value % 2 === 0;
		};

		$iterator = new FilterIterator(new ArrayIterator($data), $callback);

		$expected = [
			0 => 0,
			2 => 2,
			4 => 4,
			6 => 6,
			8 => 8,
			10 => 10,
		];

		$results = [];

		foreach ($iterator as $key => $value) {
			$results[$key] = $value;
		}

		$this->assertSame($expected, $results);
		$this->assertSame($expected, $iterator->toArray());
	}

}
