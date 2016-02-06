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

namespace Jyxo\Time;

/**
 * Tests for the \Jyxo\Time\Composer class.
 *
 * @see \Jyxo\Time\Composer
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class ComposerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests invalid data.
	 */
	public function testInvalidDates()
	{
		$composer = new Composer();

		// Invalid date/time parts
		$units = [
			'second' => [-1, 61],
			'minute' => [-1, 61],
			'hour' => [-1, 24],
			'day' => [0, 32],
			'month' => [0, 13],
			'year' => [1901, 2038]
		];
		foreach ($units as $unit => $tests) {
			foreach ($tests as $test) {
				try {
					$composer->{'set' . ucfirst($unit)}($test);
				} catch (\Exception $e) {
					$this->assertInstanceOf(\Jyxo\Time\ComposerException::class, $e);
					$this->assertSame(
						constant('\Jyxo\Time\ComposerException::' . strtoupper($unit)),
						$e->getCode(),
						sprintf('Failed test for unit %s and value %s.', $unit, $test)
					);
				}
			}
		}

		// Incomplete date
		try {
			$date = $composer->getTime();
		} catch (\Exception $e) {
			$this->assertInstanceOf(\Jyxo\Time\ComposerException::class, $e);
			$this->assertSame(ComposerException::NOT_COMPLETE, $e->getCode());
		}

		// Invalid dates
		$tests = [
			'2002-04-31',
			'2003-02-29',
			'2004-02-30',
			'2005-06-31',
			'2006-09-31',
			'2007-11-31'
		];
		foreach ($tests as $test) {
			try {
				list($year, $month, $day) = explode('-', $test);
				$composer->setDay((int) $day)
					->setMonth((int) $month)
					->setYear((int) $year);
				$time = $composer->getTime();
			} catch (\Exception $e) {
				$this->assertInstanceOf(\Jyxo\Time\ComposerException::class, $e);
				$this->assertSame(
					ComposerException::INVALID,
					$e->getCode(),
					sprintf('Failed test for %s.', $test)
				);
			}
		}
	}

	/**
	 * Tests valid date.
	 */
	public function testValidDates()
	{
		$composer = new Composer();

		$tests = [
			'2002-04-30 00:00:00',
			'2003-02-28 00:00:00',
			'2004-02-29 05:03:16',
			'2005-07-31 01:01:01',
			'2006-10-31 23:59:59',
			'2007-11-30 15:16:17'
		];
		foreach ($tests as $test) {
			preg_match('~^(\\d{4})-(\\d{2})-(\\d{2}) (\\d{2}):(\\d{2}):(\\d{2})$~', $test, $matches);
			$composer->setDay((int) $matches[3])
				->setMonth((int) $matches[2])
				->setYear((int) $matches[1])
				->setHour((int) $matches[4])
				->setMinute((int) $matches[5])
				->setSecond((int) $matches[6]);
			$time = $composer->getTime();
			$this->assertEquals(
				new Time($test),
				$time,
				sprintf('Failed test for %s.', $test)
			);
		}
	}
}
