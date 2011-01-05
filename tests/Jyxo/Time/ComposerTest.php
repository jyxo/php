<?php

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

require_once __DIR__ . '/../../bootstrap.php';

/**
 * Tests for the Jyxo_Time_Composer class.
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
		$composer = new \Jyxo\Time\Composer();

		// Invalid date/time parts
		$units = array(
			'second' => array(-1, 61, '-1', '61'),
			'minute' => array(-1, 61, '-1', '61'),
			'hour' => array(-1, 24, '-1', '24'),
			'day' => array(0, 32, '0', '32'),
			'month' => array(0, 13, '0', '13'),
			'year' => array(1901, 2038, '1901', '2038')
		);
		foreach ($units as $unit => $tests) {
			foreach ($tests as $test) {
				try {
					$composer->{'set' . ucfirst($unit)}($test);
				} catch (\Jyxo\Time\ComposerException $e) {
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
		} catch (\Jyxo\Time\ComposerException $e) {
			$this->assertSame(\Jyxo\Time\ComposerException::NOT_COMPLETE, $e->getCode());
		}

		// Invalid dates
		$tests = array(
			'2002-04-31',
			'2003-02-29',
			'2004-02-30',
			'2005-06-31',
			'2006-09-31',
			'2007-11-31'
		);
		foreach ($tests as $test) {
			try {
				list($year, $month, $day) = explode('-', $test);
				$composer->setDay($day)
					->setMonth($month)
					->setYear($year);
				$time = $composer->getTime();
			} catch (\Jyxo\Time\ComposerException $e) {
				$this->assertSame(
					\Jyxo\Time\ComposerException::INVALID,
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
		$composer = new \Jyxo\Time\Composer();

		$tests = array(
			'2002-04-30 00:00:00',
			'2003-02-28 00:00:00',
			'2004-02-29 05:03:16',
			'2005-07-31 01:01:01',
			'2006-10-31 23:59:59',
			'2007-11-30 15:16:17'
		);
		foreach ($tests as $test) {
			preg_match('~^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$~', $test, $matches);
			$composer->setDay($matches[3])
				->setMonth($matches[2])
				->setYear($matches[1])
				->setHour($matches[4])
				->setMinute($matches[5])
				->setSecond($matches[6]);
			$time = $composer->getTime();
			$this->assertEquals(
				new \Jyxo\Time\Time($test),
				$time,
				sprintf('Failed test for %s.', $test)
			);
		}
	}
}
