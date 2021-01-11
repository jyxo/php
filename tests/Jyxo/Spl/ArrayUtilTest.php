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
use function array_combine;
use function array_reverse;
use function chr;
use function count;
use function date;
use function ord;
use function range;
use function strtotime;

/**
 * Test for class \Jyxo\Spl\ArrayUtil.
 *
 * @see \Jyxo\Spl\ArrayUtil
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 * @author Ondřej Nešpor
 */
class ArrayUtilTest extends TestCase
{

	/**
	 * Tests a simple integer range.
	 */
	public function testRangeInt(): void
	{
		$range = ArrayUtil::range(1, 6, static function ($current) {
			return $current + 1;
		});

		$this->assertEquals(range(1, 6), $range);
	}

	/**
	 * Tests use of custom closures.
	 */
	public function testRangeCompare(): void
	{
		$called = 0;
		$range = ArrayUtil::range(1, 6, static function ($current) {
			return $current + 1;
		}, static function ($a, $b) use (&$called) {
			$called++;

			return $a < $b;
		});

		$this->assertEquals(range(1, 6), $range);
		$this->assertEquals(count($range), $called);
	}

	/**
	 * Tests data range generation.
	 */
	public function testRangeDate(): void
	{
		$range = ArrayUtil::range('2010-03-01', '2009-11-01', static function ($current) {
			return date('Y-m-d', strtotime('first day of last month', strtotime($current)));
		});

		$expect = [
			'2010-03-01',
			'2010-02-01',
			'2010-01-01',
			'2009-12-01',
			'2009-11-01',
		];

		$this->assertEquals($expect, $range);
	}

	/**
	 * Tests the keymap() method.
	 */
	public function testKeymap(): void
	{
		$source = [];

		foreach (range(ord('a'), ord('z')) as $value) {
			$source[] = chr($value);
		}

		$traversable = new ArrayIterator($source);

		$closure = static function ($value) {
			return chr(ord('z') + ord('a') - ord($value));
		};

		$mapped = ArrayUtil::keymap($traversable, $closure);
		$this->assertSame(array_combine(array_reverse($source), $source), $mapped);

		$mapped = ArrayUtil::keymap($traversable, $closure, $closure);
		$this->assertSame(array_reverse(array_combine($source, $source)), $mapped);
	}

}
