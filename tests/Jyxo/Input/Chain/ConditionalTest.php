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

namespace Jyxo\Input\Chain;

use Jyxo\Input\Validator\IsInt;
use Jyxo\Input\Validator\LessThan;
use PHPUnit\Framework\TestCase;
use stdClass;
use function sprintf;
use function sqrt;

/**
 * Test of conditional validator \Jyxo\Input\Chain\Conditional
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 */
class ConditionalTest extends TestCase
{

	/**
	 * Tests no condition.
	 */
	public function testNullCondition(): void
	{
		// No validator -> true for any value
		$validator = new Conditional();

		foreach (['example', 42, [], new stdClass(), 1.23, true, false] as $value) {
			$this->assertTrue($validator->isValid($value));
		}
	}

	/**
	 * Tests for conditional validation (is executed only if the condition is fulfilled)
	 */
	public function testCondition(): void
	{
		static $value = 42;
		$validator = new Conditional(new IsInt());
		$validator->addValidator(new LessThan($value));
		$good = [
			$value - 1,
			(int) ($value / 2),
			sqrt($value),
			'example',
			false,
			true,
		];
		$bad = [
			$value * 2,
			(string) ($value * 2),
			(float) ($value * 2),
		];

		foreach ($good as $value) {
			$this->assertTrue(
				$validator->isValid($value),
				sprintf('Test of value %s should be true but is false.', $value)
			);
		}

		foreach ($bad as $value) {
			$this->assertFalse(
				$validator->isValid($value),
				sprintf('Test of value %s should be false but is true.', $value)
			);
		}
	}

}
