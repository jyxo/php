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

use Countable;
use InvalidArgumentException;
use Iterator;
use LimitIterator;
use function count;

/**
 * \LimitIterator which supports \Countable for transparent wrapping.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class CountableLimitIterator extends LimitIterator implements Countable
{

	/**
	 * Result counting mode - returns all inner iterator data count.
	 */
	public const MODE_PASS = 1;

	/**
	 * Result counting mode - returns number of data after applying limit
	 * For proper function inner iterator must return exact number of its items.
	 */
	public const MODE_LIMIT = 2;

	/**
	 * Defined offset.
	 *
	 * @var int
	 */
	private $offset;

	/**
	 * Defined maximum item count.
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Result counting mode - see self::MODE_* constants.
	 *
	 * @var int
	 */
	private $mode = self::MODE_PASS;

	/**
	 * Constructor.
	 *
	 * @param Iterator $iterator Source data
	 * @param int $offset Offset (Optional)
	 * @param int $count Maximum item count (Optional)
	 * @param int $mode Result counting mode
	 */
	public function __construct(Iterator $iterator, int $offset = 0, int $count = -1, int $mode = self::MODE_PASS)
	{
		if (!($iterator instanceof Countable)) {
			throw new InvalidArgumentException('Supplied iterator must be countable');
		}

		parent::__construct($iterator, $offset, $count);

		$this->offset = $offset;
		$this->count = $count;
		$this->mode = $mode;
	}

	/**
	 * Returns number of items based on result counting mode (all inner or final count after applying limit).
	 *
	 * @return int
	 */
	public function count(): int
	{
		$count = count($this->getInnerIterator());

		if ($this->mode === self::MODE_LIMIT) {
			// We want real number of results - after applying limit

			if ($this->offset !== 0) {
				// Offset from beginning
				$count -= $this->offset;
			}

			if ($this->count !== -1 && $count > $this->count) {
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
