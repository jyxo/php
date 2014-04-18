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
 * (Czech) IBAN number validator.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class IsIban extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		// Removes spaces
		$iban = preg_replace('~\\s+~', '', (string) $value);

		if (!preg_match('~^CZ(\\d{2})(\\d{4})(\\d{6})(\\d{10})$~i', $iban, $matches)) {
			return false;
		}

		list(, $control, $bankCode, $prefix, $base) = $matches;

		// Bank account number check
		if (!\Jyxo\Input\Validator::isBankAccountNumber(sprintf('%s-%s/%s', $prefix, $base, $bankCode))) {
			return false;
		}

		// Control code check
		$temp = $bankCode . $prefix . $base . '1235' . $control;	// 1235 = CZ
		$mod = (int) substr($temp, 0, 9) % 97;
		$mod = (int) ($mod . substr($temp, 9, 7)) % 97;
		$mod = (int) ($mod . substr($temp, 16, 7)) % 97;
		$mod = (int) ($mod . substr($temp, 23)) % 97;
		if (1 !== $mod) {
			return false;
		}

		return true;
	}
}
