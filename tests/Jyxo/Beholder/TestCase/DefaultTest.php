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

namespace Jyxo\Beholder\TestCase;

/**
 * Abstract base class for beholder tests.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
abstract class DefaultTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Autoload list.
	 *
	 * @var array
	 */
	private $autoloadList = [];

	/**
	 * Turns autoloading off.
	 */
	protected function disableAutoload()
	{
		$this->autoloadList = spl_autoload_functions();
		foreach ($this->autoloadList as $function) {
			spl_autoload_unregister($function);
		}
	}

	/**
	 * Turns autoloading on.
	 */
	protected function enableAutoload()
	{
		foreach ($this->autoloadList as $function) {
			spl_autoload_register($function);
		}
		$this->autoloadList = [];
	}
}
