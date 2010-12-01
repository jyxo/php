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
 * Test pro třídu \Jyxo\Spl\Object.
 *
 * @see \Jyxo\Spl\Object
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Kompletní test.
	 */
	public function test()
	{
		require_once DIR_FILES . '/spl/Foo.php';

		$foo = new \Foo();

		// Správné jméno třídy
		$this->assertEquals('Foo', $foo->getClass());

		// Nastavení hodnot
		$foo->x = 1;
		$foo->y = true;

		// Nastavení neexistující proměnné
		$foo->z = 'test';

		// Získání hodnot
		$this->assertSame(1, $foo->x);
		$this->assertTrue($foo->y);

		// Získání hodnoty neexistující proměnné
		$this->assertNull($foo->z);

		// Testování existence proměnných
		$this->assertTrue(isset($foo->x));
		$this->assertTrue(isset($foo->y));
		$this->assertFalse(isset($foo->z));
	}
}
