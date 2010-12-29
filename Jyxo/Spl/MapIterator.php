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

/**
 * Iterator which applies a callback over results (lazy-loaded calls).
 * Supports iteration over both Traversable and array.
 *
 * @category Jyxo
 * @package Jyxo\Spl
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class MapIterator implements \Countable, \Jyxo\Spl\ArrayCopy, \OuterIterator, \SeekableIterator
{
	/**
	 * Source data we iterate over.
	 *
	 * @var \Iterator
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
	 * @throws \InvalidArgumentException Invalid source data or callback is not callable
	 */
	public function __construct($data, $map)
	{
		if (is_array($data)) {
			$this->iterator = new \ArrayIterator($data);
		} elseif ($data instanceof \IteratorAggregate) {
			$this->iterator = $data->getIterator();
		} elseif ($data instanceof \Iterator) {
			$this->iterator = $data;
		} else {
			throw new \InvalidArgumentException('Supplied data argument is not traversable.');
		}
		if (!is_callable($map)) {
			throw new \InvalidArgumentException('Supplied callback is not callable.');
		}

		$this->map = $map;
	}

	/**
	 * Returns count of source data.
	 *
	 * @return integer
	 */
	public function count()
	{
		$count = 0;
		if ($this->iterator instanceof \Countable) {
			$count = count($this->iterator);
		} else {
			$count = iterator_count($this->iterator);
		}
		return $count;
	}

	/**
	 * Rewinds the iterator to the beginning.
	 */
	public function rewind()
	{
		$this->iterator->rewind();
	}

	/**
	 * Advances the internal pointer.
	 */
	public function next()
	{
		$this->iterator->next();
	}

	/**
	 * Returns if current pointer position is valid.
	 *
	 * @return boolean
	 */
	public function valid()
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
	 * @return integer
	 */
	public function key()
	{
		return $this->iterator->key();
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

	/**
	 * Returns all data as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array_map($this->map, iterator_to_array($this->iterator));
	}

	/**
	 * Returns inner iterator (works even when constructed with array data)
	 *
	 * @return \Iterator
	 */
	public function getInnerIterator()
	{
		return $this->iterator;
	}

	/**
	 * Seeks to defined position. Does NOT throw OutOfBoundsException.
	 *
	 * @param integer $position New position
	 */
	public function seek($position)
	{
		if ($this->iterator instanceof \SeekableIterator) {
			try {
				$this->iterator->seek($position);
			} catch (\OutOfBoundsException $e) {
				// Skipped on purpose, I don't think it's necessary
				// If you'd like to have this exception throw, remove this try-catch and add to 'else' block
				// if (!$this->valid()) { throw new OutOfBoundException('Invalid seek position'); };
			}
		} else {
			$this->rewind();
			for ($i = 0; $i < $position; $i++) {
				$this->next();
			}
		}
	}
}
