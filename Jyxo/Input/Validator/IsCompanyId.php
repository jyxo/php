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

namespace Jyxo\Input\Validator;

/**
 * Validates IČ (Czech company number).
 *
 * Taken from David Grudl's http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class IsCompanyId extends \Jyxo\Input\Validator\AbstractValidator
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
		$companyId = preg_replace('~\s+~', '', (string) $value);

		// Only numbers
		if (!preg_match('~^\d{8}$~', $companyId)) {
			return false;
		}

		// Checksum
		$a = 0;
		for ($i = 0; $i < 7; $i++) {
			$a += $companyId[$i] * (8 - $i);
		}

		$a = $a % 11;
		if (0 === $a) {
			$c = 1;
		} elseif (10 === $a) {
			$c = 1;
		} elseif (1 === $a) {
			$c = 0;
		} else {
			$c = 11 - $a;
		}

		if ((int) $companyId[7] !== $c) {
			return false;
		}

		return true;
	}
}
