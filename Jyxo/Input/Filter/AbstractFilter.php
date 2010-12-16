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

namespace Jyxo\Input\Filter;

/**
 * Parent class of all filters.
 * Allows multidimensional arrays filtering.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Filter
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
abstract class AbstractFilter implements \Jyxo\Input\FilterInterface
{
	/**
	 * Creates a filter instance.
	 */
	public function __construct()
	{}

	/**
	 * Filters a value.
	 *
	 * @param mixed $value Input value
	 * @return mixed
	 */
	public static function filtrate($value)
	{
		$filter = new static();
		return $filter->filter($value);
	}

	/**
	 * Actually filters a value.
	 *
	 * @param mixed $in Input value
	 * @return mixed
	 */
	abstract protected function filterValue($in);

	/**
	 * Filters a value.
	 *
	 * @param mixed $in Object to be filtered
	 * @return \Jyxo\Input\FilterInterface This object instance
	 */
	public function filter($in)
	{
		if (is_array($in)) {
			return array_map(array($this, 'filter'), $in);
		}
		return $this->filterValue($in);
	}
}
