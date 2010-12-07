<?php

/**
 * Jyxo Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/jyxo/php/blob/master/license.txt
 */

namespace Jyxo\Gettext\Parser;

/**
 * Container for PO file headers.
 *
 * @category Jyxo
 * @package Jyxo\Gettext\Parser
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Matěj Humpál <libs@jyxo.com>
 */
class Header
{
	/**
	 * Constructor.
	 *
	 * @param string $items PO file header
	 */
	public function __construct($items)
	{
		$items = explode("\n", $items);
		foreach ($items as $item) {
			$item = trim($item, '"');
			$array = explode(': ', $item);
			if (!empty($array[0])) {
				if (!empty($array[1])) {
					$this->{$array[0]} = $array[1];
				}
			}
		}
	}
}
