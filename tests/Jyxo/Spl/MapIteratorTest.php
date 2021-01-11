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
use Closure;
use Foo2;
use Foo3;
use InvalidArgumentException;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;
use function array_map;
use function count;
use function get_class;
use function is_array;
use function range;

/**
 * Test for class \Jyxo\Spl\MapIterator.
 *
 * @see \Jyxo\Spl\ObjectCache
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Nešpor
 */
class MapIteratorTest extends TestCase
{

	/**
	 * Tests the whole class.
	 */
	public function testIterator(): void
	{
		require_once DIR_FILES . '/spl/Foo2.php';
		require_once DIR_FILES . '/spl/Foo3.php';

		$data = range(0, 10);
		$callback = static function ($value) {
			return $value * $value;
		};

		$this->runMapTest($data, $data, $callback);
		$this->runMapTest($data, new ArrayIterator($data), $callback);
		$this->runMapTest($data, new Foo2($data), $callback);
		$this->runMapTest($data, new Foo3($data), $callback);
	}

	/**
	 * Tests an invalid data argument.
	 */
	public function testInvalidDataArgument(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$iterator = new MapIterator(new stdClass(), 'str_shuffle');
	}

	/**
	 * Runs a test on the input
	 *
	 * @param array $originalData Original data for Iterators
	 * @param array|Traversable $subject \Iterator input
	 * @param Closure $callback Mapping callback
	 */
	private function runMapTest(array $originalData, $subject, Closure $callback): void
	{
		$iterator = new MapIterator($subject, $callback);

		$this->assertSame(count($originalData), count($iterator));

		foreach ($iterator as $key => $value) {
			$this->assertArrayHasKey($key, $originalData);
			$this->assertSame($callback($originalData[$key]), $value);
		}

		$iterator->seek(5);
		$this->assertSame($callback($originalData[5]), $iterator->current());

		$this->assertSame(array_map($callback, $originalData), $iterator->toArray());

		if (is_array($subject)) {
			$this->assertInstanceOf(ArrayIterator::class, $iterator->getInnerIterator());
		} elseif ($subject instanceof IteratorAggregate) {
			$this->assertInstanceOf(get_class($subject->getIterator()), $iterator->getInnerIterator());
		} else {
			$this->assertInstanceOf(get_class($subject), $iterator->getInnerIterator());
		}
	}

}
