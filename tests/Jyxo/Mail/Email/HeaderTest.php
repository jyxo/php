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

namespace Jyxo\Mail\Email;

/**
 * \Jyxo\Mail\Email\Header class test.
 *
 * @see \Jyxo\Mail\Email\Header
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class HeaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Runs the test.
	 */
	public function test()
	{
		$name = 'Organization';
		$value = 'Jyxo';

		// Email and name given
		$header = new Header($name, $value);
		$this->assertEquals($name, $header->getName());
		$this->assertEquals($value, $header->getValue());
	}
}
