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

namespace Jyxo;

/**
 * Base exception class used throughout Jyxo libraries.
 *
 * @category Jyxo
 * @package Jyxo\ErrorHandling
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Exception extends \Exception
{
	/**
	 * Returns all previous exceptions in an array.
	 *
	 * @return array
	 */
	public function getAllPrevious()
	{
		$stack = array();
		$previous = $this->getPrevious();
		while (null !== $previous) {
			$stack[] = $previous;
			$previous = $previous->getPrevious();
		}
		return $stack;
	}
}
