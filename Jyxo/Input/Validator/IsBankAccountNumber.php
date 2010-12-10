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
 * (Czech) bank number validator.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class IsBankAccountNumber extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		if (!preg_match('~^(?:(\d{1,6})-)?(\d{2,10})/(\d{4})$~i', (string) $value, $matches)) {
			return false;
		}

		list(, $prefix, $base, $bankCode) = $matches;

		// Bank codes valid in the Czech Republic on 2008-09-01
		static $bankCodes = array(
			'0100', '0300', '0400', '0600', '0700', '0800', '2010', '2020', '2030', '2040',
			'2050', '2070', '2100', '2200', '2400', '2600', '2700', '3500', '4000', '4300',
			'5000', '5400', '5500', '5800', '6000', '6100', '6200', '6210', '6300', '6700',
			'6800', '7910', '7940', '7950', '7960', '7970', '7980', '7990', '8030', '8040',
			'8060', '8090', '8150', '8200'
		);

		// Valid bank code
		if (!in_array($bankCode, $bankCodes)) {
			return false;
		}

		// Prefix check
		$prefix = str_pad($prefix, 6, '0', STR_PAD_LEFT);
		$mod = ($prefix[0] * 10 + $prefix[1] * 5 + $prefix[2] * 8 + $prefix[3] * 4 + $prefix[4] * 2 + $prefix[5] * 1) % 11;
		if (0 !== $mod) {
			return false;
		}

		// Base number part check
		$base = str_pad($base, 10, '0', STR_PAD_LEFT);
		$mod = ($base[0] * 6 + $base[1] * 3 + $base[2] * 7 + $base[3] * 9 + $base[4] * 10 + $base[5] * 5 + $base[6] * 8 + $base[7] * 4 + $base[8] * 2 + $base[9] * 1) % 11;
		if (0 !== $mod) {
			return false;
		}

		return true;
	}
}
