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

require_once __DIR__ . '/../bootstrap.php';

/**
 * The complete Jyxo PHP Library test suite.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Nešpor
 */
class AllTests
{
	/**
	 * Runs testing.
	 */
	public static function main()
	{
		\PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	/**
	 * Creates the test suite.
	 *
	 * @return \PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new \PHPUnit_Framework_TestSuite('Jyxo');

		// Walks through the whole tests directory and includes all files except the "AllTests" suites
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__)) as $file) {
			if ($file->isFile() && strtolower($file->getFileName()) !== 'alltests.php' && strtolower(substr($file->getFilename(), -4)) == '.php') {
				$fullName = substr($file->getPathName(), strlen(__DIR__), -4);
				$className = 'Jyxo' . str_replace(DIRECTORY_SEPARATOR, '\\', $fullName);

				$suite->addTestSuite($className);
			}
		}

		return $suite;
	}
}
