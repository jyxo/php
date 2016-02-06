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

/**
 * Iterator which uses a callback or closure for filtering data.
 *
 * @category Jyxo
 * @package Jyxo\Spl
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class FilterIterator extends \FilterIterator implements \Jyxo\Spl\ArrayCopy
{
	/**
	 * Callback which decides if an item is valid. Returns boolean, has one required parameter.
	 *
	 * @var \Closure|callback
	 */
	private $callback;

	/**
	 * Constructor.
	 *
	 * @param \Iterator $iterator Source data
	 * @param \Closure|callback $callback Filter callback
	 * @throws \InvalidArgumentException Supplied callback is not callable
	 */
	public function __construct(\Iterator $iterator, callable $callback)
	{
		parent::__construct($iterator);
		$this->callback = $callback;
	}

	/**
	 * Decides if an item is valid by calling a callback.
	 *
	 * @return boolean
	 */
	public function accept()
	{
		$callback = $this->callback;
		return $callback($this->current());
	}

	/**
	 * Returns all filtered data as an array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return iterator_to_array($this);
	}
}
