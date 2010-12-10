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

namespace Jyxo\Input\Validator;

require_once __DIR__ . '/../../../bootstrap.php';

/**
 * Test of \Jyxo\Input\Validator\StringLengthBetween validator.
 *
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek <libs@jyxo.com>
 */
class StringLengthBetweenTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests right values.
	 */
	public function testValid()
	{
		$testcases = array(
			array('ano', 0, 3),
			array('ano', 3, 4),
			array('ano', 1, PHP_INT_MAX),
			array('žluťoučký', 9, 9),
			array('žluťoučký', 8, 9),
			array('Žluťoučký kůn pěl ďábelské ódy', 0, 30),
			array('Žluťoučký kůn pěl ďábelské ódy', 15, 45),
			array('Žluťoučký kůn pěl ďábelské ódy', 30, 60),
			array('瑞鶴', 1, 3),
			array('瑞鶴', 2, 2)
		);

		foreach ($testcases as $testcase) {
			list($str, $min, $max) = $testcase;

			$validator = new \Jyxo\Input\Validator\StringLengthBetween($min, $max);
			$message = sprintf('Failed validation of "%s" in range(%d, %d)', $str, $min, $max);
			$this->assertTrue($validator->isValid($str), $message);
		}
	}

	/**
	 * Tests wrong values.
	 */
	public function testInvalid()
	{
		$testcases = array(
			array('ano', 4, 8),
			array('ano', 0, 2),
			array('žluťoučký', 0, 8),
			array('žluťoučký', 10, PHP_INT_MAX),
			array('Žluťoučký kůn pěl ďábelské ódy', 0, 29),
			array('Žluťoučký kůn pěl ďábelské ódy', 31, 31),
			array('Žluťoučký kůn pěl ďábelské ódy', 31, 42),
			array('瑞鶴', 0, 1),
			array('瑞鶴', 3, 128)
		);

		foreach ($testcases as $testcase) {
			list($str, $min, $max) = $testcase;

			$validator = new \Jyxo\Input\Validator\StringLengthBetween($min, $max);
			$message = sprintf('Passed validation of "%s" in range(%d, %d)', $str, $min, $max);
			$this->assertFalse($validator->isValid($str), $message);
		}
	}

	/**
	 * Tests setting an invalid lower bound.
	 */
	public function testInvalidMin()
	{
		$this->setExpectedException('InvalidArgumentException');
		$validator = new \Jyxo\Input\Validator\StringLengthBetween(-1, 5);
	}

	/**
	 * Tests setting an invalid upper bound.
	 */
	public function testInvalidMax()
	{
		$this->setExpectedException('InvalidArgumentException');
		$validator = new \Jyxo\Input\Validator\StringLengthBetween(0, -6);
	}

	/**
	 * Tests setting an invalid bounds combination.
	 */
	public function testInvalidMinMax()
	{
		$this->setExpectedException('InvalidArgumentException');
		$validator = new \Jyxo\Input\Validator\StringLengthBetween(12, 6);
	}

	/**
	 * Tests setters and getters work.
	 */
	public function testGettersSetters()
	{
		$testcases = array(
			array(0, 3, 1, 4),
			array(0, PHP_INT_MAX, 1, PHP_INT_MAX - 1),
			array(24, 42, 30, 60)
		);

		foreach ($testcases as $testcase) {
			list ($min, $max, $newMin, $newMax) = $testcase;
			$validator = new \Jyxo\Input\Validator\StringLengthBetween($min, $max);

			$this->assertEquals($min, $validator->getMin(), 'minimum not set');
			$this->assertEquals($max, $validator->getMax(), 'maximum not set');
			$this->assertEquals(get_class($validator), get_class($validator->setMin($newMin)), 'setMin does not support fluent interface');
			$this->assertEquals(get_class($validator), get_class($validator->setMax($newMax)), 'setMax does not support fluent interface');
			$this->assertEquals($newMin, $validator->getMin(), 'minimum not changed');
			$this->assertEquals($newMax, $validator->getMax(), 'maximum not changed');
		}
	}

	/**
	 * Tests setting an invalid lower bound using a setter.
	 */
	public function testInvalidSetMin()
	{
		$this->setExpectedException('InvalidArgumentException');
		$validator = new \Jyxo\Input\Validator\StringLengthBetween(2, 6);
		$validator->setMin(12);
	}

	/**
	 * Tests setting an invalid upper bound using a setter.
	 */
	public function testInvalidSetMax()
	{
		$this->setExpectedException('InvalidArgumentException');
		$validator = new \Jyxo\Input\Validator\StringLengthBetween(12, 60);
		$validator->setMax(4);
	}
}
