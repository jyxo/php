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

use function checkdate;
use function preg_match;

/**
 * Validator checking if the input value is valid date and time in YYYY-MM-DD HH::MM::SS format.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class IsDateTime extends AbstractValidator
{

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		// Format check
		if (!preg_match('~^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})$~', (string) $value, $matches)) {
			return false;
		}

		[, $year, $month, $day, $hour, $minute, $second] = $matches;

		// Date validity check
		if (!checkdate((int) $month, (int) $day, (int) $year)) {
			return false;
		}

		// Time validity check
		if ($hour < 0 || $hour > 23) {
			return false;
		}

		if ($minute < 0 || $minute > 59) {
			return false;
		}

		return $second >= 0 && $second <= 59;
	}

}
