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

namespace Jyxo\Input\Filter;

use function intval;

/**
 * Filter for converting to integers.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class ToInt extends AbstractFilter
{

	/**
	 * Base we are converting to.
	 *
	 * @var int
	 */
	private $base;

	/**
	 * Constructor.
	 *
	 * @param int $base
	 */
	public function __construct(int $base = 10)
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
