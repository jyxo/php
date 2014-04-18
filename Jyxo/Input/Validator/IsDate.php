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

namespace Jyxo\Input\Validator;

/**
 * Validates if the given value is a valid date in the YYYY-MM-DD form.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class IsDate extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		$value = (string) $value;

		// Form check
		if (!preg_match('~^(\\d{4})-(\\d{2})-(\\d{2})$~', $value, $matches)) {
			return false;
		}

		list(, $year, $month, $day) = $matches;

		// Date validity check
		if (!checkdate($month, $day, $year)) {
			return false;
		}

		return true;
	}
}
