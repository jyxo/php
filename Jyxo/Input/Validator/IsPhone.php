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

use function preg_match;
use function preg_replace;

/**
 * (Czech and Slovak) phone number validator.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 * @author Jakub Tománek
 */
class IsPhone extends AbstractValidator
{

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		// Removes spaces
		$phoneNumber = preg_replace('~\\s+~', '', (string) $value);

		if (preg_match('~^1\\d{2,8}$~', $phoneNumber)) {
			// Special numbers
			return true;
		}

		if (preg_match('~^8\\d{8}$~', $phoneNumber)) {
			// Numbers with special tariffs
			return true;
		}

		return preg_match('~^(?:(?:[+]|00)42(?:0|1))?[2-79]\\d{8}$~', $phoneNumber) === 1;
	}

}
