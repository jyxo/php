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

namespace Jyxo\Rpc;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Sada všech testů \Jyxo\Rpc.
 *
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class AllTests
{
	/**
	 * Spustí testování.
	 */
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	/**
	 * Vytvoří sadu testů.
	 *
	 * @return \PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new \PHPUnit_Framework_TestSuite('Jyxo Rpc');

		$suite->addTestSuite('\Jyxo\Rpc\Json\ServerTest');
		$suite->addTestSuite('\Jyxo\Rpc\Xml\ServerTest');

		return $suite;
	}
}
