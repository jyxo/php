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

/**
 * Filters phone numbers. Removes spaces and adds the pre-dial where possible.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Filter
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Phone extends \Jyxo\Input\Filter\AbstractFilter
{
	/**
	 * Filters a value.
	 *
	 * @param mixed $value Input value
	 * @return mixed
	 */
	protected function filterValue($value)
	{
		// Removes spaces
		$value = preg_replace('~\\s+~', '', (string) $value);

		// Adds the Czech pre-dial where possible
		if (preg_match('~^[2-79]\\d{8}$~', $value)) {
			$value = '+420' . $value;
		}

		return $value;
	}
}
