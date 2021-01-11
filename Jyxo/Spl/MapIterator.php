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
use Countable;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use OuterIterator;
use OutOfBoundsException;
use SeekableIterator;
use function array_map;
use function call_user_func;
use function count;
use function is_array;
use function iterator_count;
use function iterator_to_array;

/**
 * Iterator which applies a callback over results (lazy-loaded calls).
 * Supports iteration over both \Traversable and array.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class MapIterator implements Countable, ArrayCopy, OuterIterator, SeekableIterator
{

	/**
	 * Source data we iterate over.
	 *
	 * @var Iterator
	 */
	private $iterator;

	/**
	 * Mapping callback applied to each item.
	 *
	 * @var callback|Closure
	 */
	private $map;

	/**
	 * Constructor.
	 *
	 * @param array|Iterator|IteratorAggregate $data Source data
	 * @param callback|Closure $map Applied callback or closure
	 */
	public function __construct($data, callable $map)
	{
		if (is_array($data)) {
			$this->iterator = new ArrayIterator($data);
		} elseif ($data instanceof IteratorAggregate) {
			$this->iterator = $data->getIterator();
		} elseif ($data instanceof Iterator) {
			$this->iterator = $data;
		} else {
			throw new InvalidArgumentException('Supplied data argument is not traversable.');
		}

		$this->map = $map;
	}

	/**
	 * Returns count of source data.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return $this->iterator instanceof Countable ? count($this->iterator) : iterator_count($this->iterator);
	}

	/**
	 * Rewinds the iterator to the beginning.
	 */
	public function rewind(): void
	{
		$this->iterator->rewind();
	}

	/**
	 * Advances the internal pointer.
	 */
	public function next(): void
	{
		$this->iterator->next();
	}

	/**
	 * Returns if current pointer position is valid.
	 *
	 * @return bool
	 */
	public function valid(): bool
	{
		return $this->iterator->valid();
	}

	/**
	 * Returns current data.
	 *
	 * @return mixed
	 */
	public function current()
	{
		return $this->map($this->iterator->current());
	}

	/**
	 * Returns current key.
	 *
	 * @return int
	 */
	public function key(): int
	{
		return $this->iterator->key();
	}

	/**
	 * Returns all data as an array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return array_map($this->map, iterator_to_array($this->iterator));
	}

	/**
	 * Returns inner iterator (works even when constructed with array data)
	 *
	 * @return Iterator
	 */
	public function getInnerIterator(): Iterator
	{
		return $this->iterator;
	}

	/**
	 * Seeks to defined position. Does NOT throw {@link \OutOfBoundsException}.
	 *
	 * @param int $position New position
	 */
	public function seek(int $position): void
	{
		if ($this->iterator instanceof SeekableIterator) {
			try {
				$this->iterator->seek($position);
			} catch (OutOfBoundsException $e) {
				// Skipped on purpose, I don't think it's necessary
				// If you'd like to have this exception throw, remove this try-catch and add to 'else' block
				// If (!$this->valid()) { throw new \OutOfBoundsException('Invalid seek position'); };
			}
		} else {
			$this->rewind();

			for ($i = 0; $i < $position; $i++) {
				$this->next();
			}
		}
	}

	/**
	 * Converts source data to result using a callback function.
	 *
	 * @param mixed $item Source data
	 * @return mixed
	 */
	private function map($item)
	{
		return call_user_func($this->map, $item);
	}

}
