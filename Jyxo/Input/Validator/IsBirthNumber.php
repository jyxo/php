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

namespace Jyxo\Input\Validator;

/**
 * Validates a birth number.
 *
 * Taken from David Grudl's http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class IsBirthNumber extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value): bool
	{
		$value = (string) $value;

		if (!preg_match('~^(\\d{2})(\\d{2})(\\d{2})[ /]*(\\d{3})(\\d?)$~', trim($value), $matches)) {
			return false;
		}

		list(, $year, $month, $day, $ext, $control) = $matches;

		// Until 1954 9 numbers were used; can not check
		if ('' === $control) {
			if ($year >= 54) {
				return false;
			} else {
				return true;
			}
		}

		// Control number
		$mod = ($year . $month . $day . $ext) % 11;
		// Exception for ca 1000 numbers; no such numbers since 1985
		if (10 === $mod) {
			$mod = 0;
		}
		if ((int) $control !== $mod) {
			return false;
		}

		// Date check
		$year += $year < 54 ? 2000 : 1900;

		// 20, 50 or 70 can be added to month number
		if (($month > 70) && ($year > 2003)) {
			// Females since 2004 if all 4-digit extension combinations were used that day
			$month -= 70;
		} elseif ($month > 50) {
			// Females
			$month -= 50;
		} elseif (($month > 20) && ($year > 2003)) {
			// Males since 2004 if all 4-digit extension combinations were used that day
			$month -= 20;
		}

		// Date check
		if (!checkdate((int) $month, (int) $day, (int) $year)) {
			return false;
		}

		// Ok
		return true;
	}
}
