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
 * Filter for trimming whitespace.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Filter
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček <libs@jyxo.com>
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class Trim extends \Jyxo\Input\Filter\AbstractFilter
{
	/**
	 * Filters a value.
	 *
	 * @param mixed $in
	 * @return mixed
	 */
	protected function filterValue($in)
	{
		return trim($in);
	}

	/**
	 * Filters a value.
	 *
	 * @param mixed $in Object to be filtered
	 * @return \Jyxo\Input\FilterInterface This object instance
	 */
	public function filter($in)
	{
		$in = parent::filter($in);
		// Removes empty values
		if (is_array($in)) {
			$in = array_filter($in);
		}
		return $in;
	}
}
