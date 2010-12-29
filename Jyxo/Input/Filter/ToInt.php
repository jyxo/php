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

namespace Jyxo\Input\Filter;

/**
 * Filter for converting to integers.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Filter
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class ToInt extends \Jyxo\Input\Filter\AbstractFilter
{
	/**
	 * Base we are converting to.
	 *
	 * @var integer
	 */
	private $base;

	/**
	 * Constructor.
	 *
	 * @param integer $base
	 */
	public function __construct($base = 10)
	{
		$this->base = $base;
	}

	/**
	 * Filters a value.
	 *
	 * @param mixed $in Input value
	 * @return mixed
	 */
	protected function filterValue($in)
	{
		return intval($in, $this->base);
	}
}
