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
 * Validates a IPv6 address.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class IsIpV6 extends AbstractValidator
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

		// a:b:c:d:e:f:g:h
		$patternIpV6Variant8Hex = '(?:(?:[0-9a-f]{1,4}:){7}[0-9a-f]{1,4})';
		// Compressed a::b
		$patternIpV6VariantCompressedHex = '(?:(?:(?:[0-9a-f]{1,4}(?::[0-9a-f]{1,4})*)?)::(?:(?:[0-9a-f]{1,4}(?::[0-9a-f]{1,4})*)?))';
		// IPv4 mapped to IPv6 a:b:c:d:e:f:w.x.y.z
		$patternIpV6VariantHex4Dec = '(?:(?:(?:[0-9a-f]{1,4}:){6})' . $patternIpV4 . ')';
		// Compressed IPv4 mapped to IPv6 a::b:w.x.y.z
		$patternIpV6VariantCompressedHex4Dec = '(?:(?:(?:[0-9a-f]{1,4}(?::[0-9a-f]{1,4})*)?)::(?:(?:[0-9a-f]{1,4}:)*)' . $patternIpV4 . ')';
		$patternIpV6 = '(?:' . $patternIpV6Variant8Hex . '|' . $patternIpV6VariantCompressedHex . '|' . $patternIpV6VariantHex4Dec . '|' . $patternIpV6VariantCompressedHex4Dec . ')';

		return preg_match('~^' . $patternIpV6 . '$~', (string) $value) === 1;
	}

}
