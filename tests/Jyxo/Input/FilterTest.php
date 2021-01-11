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

use PHPUnit\Framework\TestCase;

/**
 * Filter tests of package \Jyxo\Input
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Pěček
 * @author Jaroslav Hanslík
 */
class FilterTest extends TestCase
{

	/**
	 * String for testing multiple filters.
	 */
	public const TEST_STRING = ' tEst ';

	/**
	 * Desired filter chain result.
	 */
	public const TEST_STRING_RESULT = 'test';

	/**
	 * Tests Trim filter.
	 */
	public function testTrim(): void
	{
		$filter = new Filter\Trim();

		$this->filter($filter, '  test  ', 'test');

		$this->filter(
			$filter,
			['  test1', ' test2 '],
			['test1', 'test2']
		);

		// Tests multidimensional array filtering
		$this->filter(
			$filter,
			[[' ', ' ', [' ']], ' '],
			[]
		);

		$this->filterArrayTest($filter);
	}

	/**
	 * Tests LowerCase filter.
	 */
	public function testLowerCase(): void
	{
		$filter = new Filter\LowerCase();

		$this->filter($filter, 'tESt', 'test');

		$this->filter(
			$filter,
			['TEST1', 'tESt2'],
			['test1', 'test2']
		);

		// Tests multidimensional array filtering
		$this->filter(
			$filter,
			['TEST1', ['tESt2', 'TeST']],
			['test1', ['test2', 'test']]
		);

		$this->filterArrayTest($filter);
	}

	/**
	 * Tests Phone filter.
	 */
	public function testPhone(): void
	{
		$filter = new Filter\Phone();

		$this->filter(
			$filter,
			['123 456 789', '604604 604', '+420 604 604 604', 'foo bar'],
			['123456789', '+420604604604', '+420604604604', 'foobar']
		);
	}

	/**
	 * Tests SanitizeUrl filter.
	 */
	public function testSanitizeUrl(): void
	{
		// In form: expected value, input value
		$tests = [
			[
				'http://www.jyxo.cz',
				'www.jyxo.cz',
			],
			[
				'http://www.jyxo.cz',
				'http://www.jyxo.cz',
			],
			[
				'https://www.jyxo.cz',
				'https://www.jyxo.cz',
			],
		];

		$filter = new Filter\SanitizeUrl();

		foreach ($tests as $test) {
			$result = $filter->filter($test[1]);
			$this->assertEquals($test[0], $result);
		}

		$actual = 'www.jyxo.cz';
		$result = Filter\SanitizeUrl::filtrate($actual);
		$this->assertEquals('http://www.jyxo.cz', $result);
	}

	/**
	 * Common function for filter testing.
	 *
	 * @param FilterInterface $filter Filter instance
	 * @param mixed $var Input value
	 * @param mixed $expected Expected value
	 */
	private function filter(FilterInterface $filter, $var, $expected): void
	{
		$result = $filter->filter($var);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Array filtering test - uses an invalid value on input.
	 *
	 * @param FilterInterface $filter Filter instance
	 */
	private function filterArrayTest(FilterInterface $filter): void
	{
		$var = [];
		$result = $filter->filter($var);
		$this->assertSame([], $result);

		$var = 'test';
		$result = $filter->filter($var);
		$this->assertSame('test', $result);
	}

}
