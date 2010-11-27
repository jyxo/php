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

namespace Jyxo\Spl;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Sada všech testů \Jyxo\Spl
 *
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek <libs@jyxo.com>
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
		$suite = new \PHPUnit_Framework_TestSuite('Jyxo SPL');

		$suite->addTestSuite('\Jyxo\Spl\ArrayTest');
		$suite->addTestSuite('\Jyxo\Spl\CountableLimitIteratorTest');
		$suite->addTestSuite('\Jyxo\Spl\ObjectTest');
		$suite->addTestSuite('\Jyxo\Spl\ObjectCacheTest');

		return $suite;
	}
}
