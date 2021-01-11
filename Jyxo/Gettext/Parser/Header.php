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

namespace Jyxo\Gettext\Parser;

use function explode;
use function trim;

/**
 * Container for PO file headers.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál
 */
class Header
{

	/**
	 * Constructor.
	 *
	 * @param string $items PO file header
	 */
	public function __construct(string $items)
	{
		$items = explode("\n", $items);

		foreach ($items as $item) {
			$item = trim($item, '"');
			$array = explode(': ', $item);

			if (empty($array[0])) {
				continue;
			}

			if (!empty($array[1])) {
				$this->{$array[0]} = $array[1];
			}
		}
	}

}
