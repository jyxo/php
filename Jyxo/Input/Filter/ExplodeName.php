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

use function array_pop;
use function explode;
use function implode;

/**
 * Filter for splitting a name into first and second name.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class ExplodeName extends AbstractFilter
{

	/**
	 * Filters a value.
	 *
	 * @param mixed $in Input value
	 * @return mixed
	 */
	protected function filterValue($in)
	{
		$data = explode(' ', $in);

		$lastname = array_pop($data);
		$firstname = implode(' ', $data);

		$in = [
			'firstname' => $firstname,
			'lastname' => $lastname,
		];

		return $in;
	}

}
