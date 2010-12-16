<?php

/**
 * Jyxo Library
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
 * \LimitIterator which supports \Countable for transparent wrapping.
 *
 * @category Jyxo
 * @package Jyxo\Spl
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek <libs@jyxo.com>
 */
class CountableLimitIterator extends \LimitIterator implements \Countable
{
	/**
	 * Result counting mode - returns all inner iterator data count.
	 *
	 * @var integer
	 */
	const MODE_PASS = 1;

	/**
	 * Result counting mode - returns number of data after applying limit
	 * For proper function inner iterator must return exact number of its items.
	 *
	 * @var integer
	 */
	const MODE_LIMIT = 2;

	/**
	 * Defined offset.
	 *
	 * @var integer
	 */
	private $offset;

	/**
	 * Defined maximum item count.
	 *
	 * @var integer
	 */
	private $count;

	/**
	 * Result counting mode - see self::MODE_* constants.
	 *
	 * @var integer
	 */
	private $mode = self::MODE_PASS;

	/**
	 * Constructor.
	 *
	 * @param \Iterator $iterator Source data
	 * @param integer $offset Offset (Optional)
	 * @param integer $count Maximum item count (Optional)
	 * @param integer $mode Result counting mode
	 * @throws \InvalidArgumentException Inner iterator is not countable
	 */
	public function __construct (\Iterator $iterator, $offset = 0, $count = -1, $mode = self::MODE_PASS)
	{
		if (!($iterator instanceof \Countable)) {
			throw new \InvalidArgumentException('Supplied iterator must be countable');
		}

		parent::__construct($iterator, $offset, $count);
		$this->offset = $offset;
		$this->count = $count;
		$this->mode = $mode;
	}

	/**
	 * Returns number of items based on result counting mode (all inner or final count after applying limit).
	 *
	 * @return integer
	 */
	public function count()
	{
		$count = count($this->getInnerIterator());
		if (self::MODE_LIMIT === $this->mode) {
			// We want real number of results - after applying limit

			if (0 !== $this->offset) {
				// Offset from beginning
				$count -= $this->offset;
			}
			if (-1 !== $this->count && $count > $this->count) {
				// Maximum number of items
				$count = $this->count;
			}
			if ($count < 0) {
				// We moved after end of inner iterator - offset is higher than count($this->getInnerIterator())
				$count = 0;
			}
		}
		return $count;
	}
}
