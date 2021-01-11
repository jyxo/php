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

namespace Jyxo\Spl;

use Foo;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Jyxo\Spl\BaseObject.
 *
 * @see \Jyxo\Spl\BaseObject
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 * @author Ondřej Nešpor
 */
class BaseObjectTest extends TestCase
{

	/**
	 * Runs the whole test.
	 */
	public function test(): void
	{
		require_once DIR_FILES . '/spl/Foo.php';

		$foo = new Foo();

		// Right class name
		$this->assertEquals('Foo', $foo->getClass());

		// Setting parameter values
		$foo->x = 1;
		$foo->y = true;

		// Setting a non-existent parameter value
		$foo->z = 'test';

		// Fetching parameter values
		$this->assertSame(1, $foo->x);
		$this->assertTrue($foo->y);

		// Fetching value of a non-existent parameter
		$this->assertNull($foo->z);

		// Testing parameter existence
		$this->assertTrue(isset($foo->x));
		$this->assertTrue(isset($foo->y));
		$this->assertFalse(isset($foo->z));

		// Testing toArray conversion
		$array = ['x' => 1, 'y' => true];
		$this->assertEquals($array, $foo->toArray());
	}

}
