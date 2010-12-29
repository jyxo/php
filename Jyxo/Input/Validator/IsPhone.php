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
 * (Czech and Slovak) phone number validator.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 * @author Jakub Tománek
 */
class IsPhone extends \Jyxo\Input\Validator\AbstractValidator
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
		$phoneNumber = preg_replace('~\s+~', '', (string) $value);

		if (preg_match('~^1\d{2,8}$~', $phoneNumber)) {
			// Special numbers
			return true;
		} elseif (preg_match('~^8\d{8}$~', $phoneNumber)) {
			// Numbers with special tariffs
			return true;
		} elseif (preg_match('~^(?:(?:[+]|00)42(?:0|1))?[2-79]\d{8}$~', $phoneNumber)) {
			// Normal numbers
			return true;
		} else {
			return false;
		}
	}
}
