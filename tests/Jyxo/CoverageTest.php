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
 * Does not test anything, just loads all files to be able to generate the whole code coverage report.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class CoverageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Loads all files in the Library.
	 */
	public function testRequire()
	{
		$dir = realpath(__DIR__ . '/../..');

		foreach (new \DirectoryIterator($dir) as $file) {
			/* @var $file \DirectoryIterator */
			if ($file->isDir()) {
				$name = $file->getFilename();
				if ($name[0] != '.' && $name != 'examples' && $name != 'tests') {
					$objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($file->getPathname()));
					foreach ($objects as $file) {
						if ($file->isFile() && strtolower(substr($file->getFilename(), -4)) == '.php') {
							require_once $file->getPathname();
						}
					}
				}
			}
		}
	}
}
