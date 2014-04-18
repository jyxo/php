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
 * Validates a URL.
 *
 * @category Jyxo
 * @package Jyxo\Input
 * @subpackage Validator
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class IsUrl extends \Jyxo\Input\Validator\AbstractValidator
{
	/**
	 * Validates a value.
	 *
	 * @param mixed $value Input value
	 * @return boolean
	 */
	public function isValid($value)
	{
		$patternGenericTld = '(?:aero|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|asia|post|geo)';
		$patternTld = '(?-i:' . $patternGenericTld . '|[a-z]{2})';
		$patternDomain = '(?:(?:[a-z]|[a-z0-9](?:[\-a-z0-9]{0,61}[a-z0-9]))[.])*(?:[a-z0-9](?:[\-a-z0-9]{0,61}[a-z0-9])[.]' . $patternTld . ')';

		// protocol://user:password@
		$patternUrl = '(?:(?:http|ftp)s?://(?:[\\S]+(?:[:][\\S]*)?@)?)?';
		// domain.tld
		$patternUrl .= '(?:' . $patternDomain . ')';
		// :port/path/file.extension
		$patternUrl .= '(?::[0-9]+)?(?:(?:/+[\-\\w\\pL\\pN\~.,:!%]+)*(?:/|[.][a-z0-9]{2,4})?)?';
		// ?query#hash
		$patternUrl .= '(?:[?&][\]\[\-\\w\\pL\\pN.,?!\~%#@&;:/\'\=+]*)?(?:#[\]\[\-\\w\\pL\\pN.,?!\~%@&;:/\'\=+]*)?';

		if (!preg_match('~^' . $patternUrl . '$~i', (string) $value)) {
			return false;
		}

		return true;
	}
}
