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
 * Validates IČ (Czech company number).
 *
 * Taken from David Grudl's http://latrine.dgx.cz/jak-overit-platne-ic-a-rodne-cislo
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class IsCompanyId extends AbstractValidator
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
		$companyId = preg_replace('~\\s+~', '', (string) $value);

		// Only numbers
		if (!preg_match('~^\\d{8}$~', $companyId)) {
			return false;
		}

		// Checksum
		$a = 0;

		for ($i = 0; $i < 7; $i++) {
			$a += $companyId[$i] * (8 - $i);
		}

		$a %= 11;

		if ($a === 0) {
			$c = 1;
		} elseif ($a === 10) {
			$c = 1;
		} elseif ($a === 1) {
			$c = 0;
		} else {
			$c = 11 - $a;
		}

		return (int) $companyId[7] === $c;
	}

}
