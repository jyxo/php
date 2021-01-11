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

namespace Jyxo\Input\Validator;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use function sprintf;
use const PHP_INT_MAX;

/**
 * Test of \Jyxo\Input\Validator\StringLengthBetween validator.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class StringLengthBetweenTest extends TestCase
{

	/**
	 * Tests right values.
	 */
	public function testValid(): void
	{
		$testcases = [
			['ano', 0, 3],
			['ano', 3, 4],
			['ano', 1, PHP_INT_MAX],
			['žluťoučký', 9, 9],
			['žluťoučký', 8, 9],
			['Žluťoučký kůn pěl ďábelské ódy', 0, 30],
			['Žluťoučký kůn pěl ďábelské ódy', 15, 45],
			['Žluťoučký kůn pěl ďábelské ódy', 30, 60],
			['瑞鶴', 1, 3],
			['瑞鶴', 2, 2],
		];

		foreach ($testcases as $testcase) {
			[$str, $min, $max] = $testcase;

			$validator = new StringLengthBetween($min, $max);
			$message = sprintf('Failed validation of "%s" in range(%d, %d)', $str, $min, $max);
			$this->assertTrue($validator->isValid($str), $message);
		}
	}

	/**
	 * Tests wrong values.
	 */
	public function testInvalid(): void
	{
		$testcases = [
			['ano', 4, 8],
			['ano', 0, 2],
			['žluťoučký', 0, 8],
			['žluťoučký', 10, PHP_INT_MAX],
			['Žluťoučký kůn pěl ďábelské ódy', 0, 29],
			['Žluťoučký kůn pěl ďábelské ódy', 31, 31],
			['Žluťoučký kůn pěl ďábelské ódy', 31, 42],
			['瑞鶴', 0, 1],
			['瑞鶴', 3, 128],
		];

		foreach ($testcases as $testcase) {
			[$str, $min, $max] = $testcase;

			$validator = new StringLengthBetween($min, $max);
			$message = sprintf('Passed validation of "%s" in range(%d, %d)', $str, $min, $max);
			$this->assertFalse($validator->isValid($str), $message);
		}
	}

	/**
	 * Tests setting an invalid lower bound.
	 */
	public function testInvalidMin(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$validator = new StringLengthBetween(-1, 5);
	}

	/**
	 * Tests setting an invalid upper bound.
	 */
	public function testInvalidMax(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$validator = new StringLengthBetween(0, -6);
	}

	/**
	 * Tests setting an invalid bounds combination.
	 */
	public function testInvalidMinMax(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$validator = new StringLengthBetween(12, 6);
	}

	/**
	 * Tests setting an invalid lower bound using a setter.
	 */
	public function testInvalidSetMin(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$validator = new StringLengthBetween(2, 6);
		$validator->setMin(12);
	}

	/**
	 * Tests setting an invalid upper bound using a setter.
	 */
	public function testInvalidSetMax(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$validator = new StringLengthBetween(12, 60);
		$validator->setMax(4);
	}

}
