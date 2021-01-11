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

use BadMethodCallException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Throwable;
use function sprintf;
use function var_export;

/**
 * Test of \Jyxo\Input\Fluent and chained classes.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class FluentTest extends TestCase
{

	/**
	 * Tests basic work.
	 */
	public function testBasicFluent(): void
	{
		$input = new Fluent();

		$input
			->check(' 42 ', 'answer')
			->filter('trim')
			->validate('isInt');

		$this->assertTrue($input->isValid());
		$this->assertEquals(['answer' => '42'], $input->getValues());
	}

	/**
	 * Tests default value.
	 */
	public function testDefaultValue(): void
	{
		$input = new Fluent();

		$input
			->check('', 'message')
			->validate('notEmpty')
			->defaultValue('default');

		$this->assertTrue($input->isValid());
		$this->assertEquals('default', $input->message);

		$this->expectException(BadMethodCallException::class);
		$input = new Fluent();
		$input->defaultValue('default');
	}

	/**
	 * Tests walking through an array.
	 */
	public function testWalk(): void
	{
		$current = [
			'aBcDe',
			'Jakub',
			'ŽLUŤOUČKÝ kůň PĚL ďábelské ÓDY',
		];
		$expected = [
			'abcde',
			'jakub',
			'žluťoučký kůň pěl ďábelské ódy',
		];

		$input1 = new Fluent();
		$input1
			->check($current, 'data')
			->filter('lowerCase')
			->validate('isArray');
		$input2 = new Fluent();
		$input2
			->check($current, 'data')
			->walk()
			->filter('lowercase')
			->validate('stringLengthGreaterThan', '', 4);

		$this->assertTrue($input1->isValid());
		$this->assertTrue($input2->isValid());
		$this->assertEquals($expected, $input1->data);
		$this->assertEquals($expected, $input2->data);
	}

	/**
	 * Tests superglobal arrays.
	 */
	public function testSuperglobals(): void
	{
		$_REQUEST['data'] = $_POST['data'] = 'string';
		$_REQUEST['jyxo'] = $_GET['jyxo'] = '1';

		$input = new Fluent();
		$input
			->post('data')
			->validate('stringLengthGreaterThan', 'short', 5)
			->validate('stringLengthLessThan', 'long', 7)
			->query('jyxo')
			->validate('isInt')
			->request('jyxo')
			->validate('lessThan', 'big', 100);

		$this->assertTrue($input->isValid());
		$this->assertNull($input->validateAll());
	}

	/**
	 * Tests invalid input data.
	 */
	public function testInvalid(): void
	{
		$input = new Fluent();

		$input
			->check('foo', 'foo')
			->validate('isInt', 'not int');

		$this->assertFalse($input->isValid());
		$this->assertEquals(['not int'], $input->getErrors());

		$input = new Fluent();
		$input
			->check('bar', 'bar')
			->validate('isInt', 'not int');

		$this->assertFalse($input->isValid(true));
		$this->assertEquals(['bar' => ['not int']], $input->getErrors());

		$input = new Fluent();
		$input
			->check('foo', 'foo')
			->all()
			->validate('isInt');

		$this->assertFalse($input->isValid());

		$this->expectException(Exception::class);
		$input->getValue('bar');
	}

	/**
	 * Tests validation failure in the middle of a string.
	 */
	public function testInvalidWalk(): void
	{
		$current = [
			42,
			0,
			'nulák',
		];

		$input = new Fluent();
		$input
			->check($current, 'data')
			->walk(false)
			->validate('isInt');

		$this->assertFalse($input->isValid());

		try {
			$input->validateAll();
			$this->fail(sprintf('Expected exception %s.', \Jyxo\Input\Validator\Exception::class));
		} catch (AssertionFailedError $e) {
			throw $e;
		} catch (Throwable $e) {
			// Correctly thrown exception
			$this->assertInstanceOf(\Jyxo\Input\Validator\Exception::class, $e);
		}
	}

	/**
	 * Tests conditional validation.
	 */
	public function testConditional(): void
	{
		$good = [
			// Condition fulfilled, validates
			43 => '42',
			// Condition not fulfilled, no validation - true is returned
			20 => '42.23',
			20 => 'example',
			20 => [],
			20 => true,
			20 => false,
		];
		// Condition fulfilled, but validation fails
		$bad = [
			30 => '42',
			1 => '42',
		];

		// Complex value test
		foreach ([true => $good, false => $bad] as $result => $values) {
			foreach ($values as $lessThan => $value) {
				$input = new Fluent();
				$input
					->check($value, 'answer')
					->condition('isInt')
					->validate('lessThan', 'error', $lessThan);
				$this->assertEquals(
					(bool) $result,
					$input->isValid(),
					sprintf(
						'Test of value %s should be %s but is %s.',
						$value,
						var_export((bool) $result, true),
						var_export(!$result, true)
					)
				);
			}
		}

		// Deep chain test
		$input = new Fluent();
		$input
			->check(42, 'answer')
			->validate('notEmpty', 'error')
			->condition('isInt')
			->validate('lessThan', 'error', 100);
		$this->assertTrue($input->isValid());

		// Not an active variable
		$this->expectException(BadMethodCallException::class);
		$input = new Fluent();
		$input->condition('isInt');
	}

	/**
	 * Tests chain closing.
	 */
	public function testClose(): void
	{
		$input = new Fluent();
		$input
			->check(42, 'anwer')
			->validate('notEmpty', 'error')
			->condition('isInt')
			->validate('lessThan', 'error', 100)
			->close()
			->validate('isInt');
		$this->assertTrue($input->isValid());

		// But it's not a ZIP code...
		$input->validate('isZipCode');
		$this->assertFalse($input->isValid());
	}

	/**
	 * Tests adding an invalid filter.
	 */
	public function testAddInvalidFilter(): void
	{
		$this->expectException(Exception::class);
		$input = new Fluent();
		$input->filter('foo');
	}

	/**
	 * Tests adding an invalid condition.
	 */
	public function testAddInvalidCondition(): void
	{
		$this->expectException(Exception::class);
		$input = new Fluent();
		$input->condition('foo');
	}

	/**
	 * Tests adding an invalid validator.
	 */
	public function testAddInvalidValidator(): void
	{
		$this->expectException(Exception::class);
		$input = new Fluent();
		$input->validate('foo');
	}

}
