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

namespace Jyxo\Input;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * All tests suite of \Jyxo\Input.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class AllTests
{
	/**
	 * Runs testing.
	 */
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	/**
	 * Creates the test suite.
	 *
	 * @return \PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new \PHPUnit_Framework_TestSuite('Jyxo Input');

		$suite->addTestSuite('\Jyxo\Input\FactoryTest');
		$suite->addTestSuite('\Jyxo\Input\FilterTest');
		$suite->addTestSuite('\Jyxo\Input\FluentTest');
		$suite->addTestSuite('\Jyxo\Input\ValidatorTest');

		$suite->addTestSuite('\Jyxo\Input\Chain\ConditionalTest');

		$suite->addTestSuite('\Jyxo\Input\Validator\StringLengthBetweenTest');

		return $suite;
	}
}
