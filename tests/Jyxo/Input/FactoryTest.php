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

namespace Jyxo\Input;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Throwable;
use function class_exists;
use function sprintf;

require_once __DIR__ . '/../../files/input/Filter.php';
require_once __DIR__ . '/../../files/input/Validator.php';

/**
 * Test for class \Jyxo\Input\Factory
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 * @author Ondřej Nešpor
 */
class FactoryTest extends TestCase
{

	/**
	 * Factory we are testing.
	 *
	 * @var Factory
	 */
	private $factory;

	/**
	 * Tests creating an object with 0 parameters.
	 */
	public function testNoParam(): void
	{
		$validator = new Validator\IsInt();
		$filter = new Filter\Trim();

		$this->assertEquals($validator, $this->factory->getValidatorByName('isInt'));
		$this->assertEquals($filter, $this->factory->getFilterByName('trim'));
	}

	/**
	 * Tests creating an object with 1 parameter.
	 */
	public function testSingleParam(): void
	{
		$validator = new Validator\StringLengthGreaterThan(42);
		$this->assertEquals($validator, $this->factory->getValidatorByName('stringLengthGreaterThan', 42));
	}

	/**
	 * Tests creating an object with more parameters.
	 */
	public function testDoubleParam(): void
	{
		$validator = new Validator\StringLengthBetween(24, 42);
		$this->assertEquals($validator, $this->factory->getValidatorByName('stringLengthBetween', [24, 42]));
	}

	/**
	 * Tests "creating" an object defined by an instance.
	 */
	public function testGettingByInstances(): void
	{
		$filter = new Filter\Phone();
		$validator = new Validator\Equals(10);

		$this->assertSame($filter, $this->factory->getFilterByName($filter));
		$this->assertSame($validator, $this->factory->getValidatorByName($validator));
	}

	/**
	 * Tests creating a filter instance with a custom prefix.
	 */
	public function testCustomFilterPrefix(): void
	{
		$filterName = 'Filter';
		$filterPrefix = '\SomePrefix\Some\\';

		// Ensure that there is no such class loaded
		if (class_exists($filterName, false)) {
			$this->markTestSkipped(sprintf('Class %s exists', $filterName));
		}

		try {
			$this->factory->getFilterByName($filterName);
			$this->fail(sprintf('Expected exception %s.', Exception::class));
		} catch (ExpectationFailedException $e) {
			throw $e;
		} catch (Throwable $e) {
			$this->assertInstanceOf(Exception::class, $e);
		}

		$this->factory->addFilterPrefix($filterPrefix);
		$filter = $this->factory->getFilterByName($filterName);
	}

	/**
	 * Tests creating a validator instance with a custom prefix.
	 */
	public function testCustomValidatorPrefix(): void
	{
		$validatorName = 'Validator';
		$validatorPrefix = '\SomeOtherPrefix\Some\\';

		// Ensure that there is no such class loaded
		if (class_exists($validatorName, false)) {
			$this->markTestSkipped(sprintf('Class %s exists', $validatorName));
		}

		try {
			$this->factory->getValidatorByName($validatorName);
			$this->fail(sprintf('Expected exception %s.', Exception::class));
		} catch (ExpectationFailedException $e) {
			throw $e;
		} catch (Throwable $e) {
			$this->assertInstanceOf(Exception::class, $e);
		}

		$this->factory->addValidatorPrefix($validatorPrefix);
		$validator = $this->factory->getValidatorByName($validatorName);
	}

	/**
	 * Tests creating a non-existent filter.
	 */
	public function testInexistentFilter(): void
	{
		$this->expectException(Exception::class);
		$this->factory->getFilterByName('foo');
	}

	/**
	 * Tests creating a non-existent filter.
	 */
	public function testInexistentValidator(): void
	{
		$this->expectException(Exception::class);
		$this->factory->getValidatorByName('foo');
	}

	/**
	 * Sets up the test.
	 */
	protected function setUp(): void
	{
		$this->factory = new Factory();
	}

	/**
	 * Finishes the test.
	 */
	protected function tearDown(): void
	{
		$this->factory = null;
	}

}
