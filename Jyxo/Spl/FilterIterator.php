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
 * Iterator which uses callback or closure for filtering data
 *
 * @category Jyxo
 * @package Jyxo\Spl
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek <libs@jyxo.com>
 */
class FilterIterator extends \FilterIterator implements \Jyxo\Spl\ArrayCopy
{
	/**
	 * Callback which decides if item is valid. Returns boolean, has one requied parameter.
	 *
	 * @var \Closure|callback
	 */
	private $callback;

	/**
	 * Constructor
	 *
	 * @param \Iterator $iterator
	 * @param \Closure|callback $callback
	 * @throws \InvalidArgumentException Supplies callback is not callable.
	 */
	public function __construct(\Iterator $iterator, $callback)
	{
		if (!is_callable($callback)) {
			throw new \InvalidArgumentException('Callback is not callable');
		}

		parent::__construct($iterator);
		$this->callback = $callback;
	} // __construct();

	/**
	 * Decides if item is valid by calling callback.
	 *
	 * @return boolean
	 */
	public function accept()
	{
		$callback = $this->callback;
		return $callback($this->current());
	} // accept();

	/**
	 * Returns all filtered data as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return iterator_to_array($this);
	} // toArray();
}
