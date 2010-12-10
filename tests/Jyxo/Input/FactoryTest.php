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

namespace Jyxo\Input;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Test for class \Jyxo\Input\Factory
 *
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek <libs@jyxo.com>
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Factory we are testing.
	 *
	 * @var \Jyxo\Input\Factory
	 */
	private $factory;

	/**
	 * Sets up the test.
	 */
	protected function setUp()
	{
		$this->factory = new \Jyxo\Input\Factory();
	}

	/**
	 * Finishes the test.
	 */
	protected function tearDown()
	{
		$this->factory = null;
	}

	/**
	 * Tests creating an object with 0 parameters.
	 */
	public function testNoParam()
	{
		$validator = new \Jyxo\Input\Validator\IsInt();
		$filter = new \Jyxo\Input\Filter\Trim();

		$this->assertEquals($validator, $this->factory->getValidatorByName('isInt'));
		$this->assertEquals($filter, $this->factory->getFilterByName('trim'));
	}

	/**
	 * Tests creating an object with 1 parameter.
	 */
	public function testSingleParam()
	{
		$validator = new \Jyxo\Input\Validator\StringLengthGreaterThan(42);
		$this->assertEquals($validator, $this->factory->getValidatorByName('stringLengthGreaterThan', 42));
	}

	/**
	 * Tests creating an object with more parameters.
	 */
	public function testDoubleParam()
	{
		$validator = new \Jyxo\Input\Validator\StringLengthBetween(24, 42);
		$this->assertEquals($validator, $this->factory->getValidatorByName('stringLengthBetween', array(24, 42)));
	}

	/**
	 * Tests creating a non-existent filter.
	 */
	public function testInexistentFilter()
	{
		$this->setExpectedException('\Jyxo\Input\Exception');
		$this->factory->getFilterByName('foo');
	}

	/**
	 * Tests creating a non-existent filter.
	 */
	public function testInexistentValidator()
	{
		$this->setExpectedException('\Jyxo\Input\Exception');
		$this->factory->getValidatorByName('foo');
	}
}
