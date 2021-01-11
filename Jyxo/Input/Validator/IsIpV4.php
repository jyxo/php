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

/**
 * Validates a IPv4 address.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class IsIpV4 extends AbstractValidator
{

	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return bool
	 */
	public function isValid($value): bool
	{
		$pattern8bit = '(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9]?[0-9])';
		$patternIpV4 = '(?:' . $pattern8bit . '(?:[.]' . $pattern8bit . '){3})';

		return preg_match('~^' . $patternIpV4 . '$~', (string) $value) === 1;
	}

}
