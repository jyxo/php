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

namespace Jyxo\Input\Filter;

use function array_filter;
use function is_array;
use function trim;

/**
 * Filter for trimming whitespace.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 * @author Jaroslav Hanslík
 */
class Trim extends AbstractFilter
{

	/**
	 * Filters a value.
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 * @param mixed $in Object to be filtered
	 * @return string This object instance
	 */
	public function filter($in)
	{
		$in = parent::filter($in);

		// Removes empty values
		if (is_array($in)) {
			$in = array_filter($in);
		}

		return $in;
	}

	/**
	 * Filters a value.
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
	 * @param string $in
	 * @return string
	 */
	protected function filterValue($in)
	{
		return trim((string) $in);
	}

}
