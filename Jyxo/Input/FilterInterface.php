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

namespace Jyxo\Input;

/**
 * Interface defining basic filter methods.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
interface FilterInterface
{

	/**
	 * Filters a value.
	 *
	 * Value is passed by reference and therefore it gets altered.
	 *
	 * @param mixed $in Filtered value
	 * @return mixed
	 */
	public function filter($in);

}
