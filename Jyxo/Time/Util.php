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

/**
 * Various utilities for working with date/time.
 *
 * @category Jyxo
 * @package Jyxo\Time
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class Util
{
	/**
	 * List of Czech public holidays.
	 *
	 * @var array
	 */
	private static $holidays = [
		'1.1',
		'1.5',
		'8.5',
		'5.7',
		'6.7',
		'28.9',
		'28.10',
		'17.11',
		'24.12',
		'25.12',
		'26.12'
	];

	/**
	 * Returns the next month.
	 *
	 * If the current date is greater than the next month's number of days, returns the next month's last date.
	 * This is different from strtotime('+1 month') behaviour, where August 31st returns October 1st.
	 *
	 * @param integer $now Current date/time
	 * @return \Jyxo\Time\Time
	 */
	public static function nextMonth(\Jyxo\Time\Time $now = null)
	{
		$now = $now ? $now->unix : time();

		$nextMonth = date('n', $now) + 1;
		$thisYear = date('Y', $now);
		// Actual date vs. next month's number of days
		$day = min(date('j', $now), date('t', mktime(0, 0, 0, $nextMonth, 1, $thisYear)));

		// Create the date
		$date = mktime(date('H', $now), date('i', $now), date('s', $now), $nextMonth, $day, $thisYear);
		return new Time($date);
	}

	/**
	 * Checks if the given date is a working day.
	 *
	 * @param \Jyxo\Time\Time $day Date to be checked
	 * @return boolean
	 */
	public static function isWorkDay(\Jyxo\Time\Time $day)
	{
		$holidays = self::$holidays;

		// Adds Easter Monday. easter_date is supposed to be buggy http://cz.php.net/manual/en/function.easter-date.php#80664
		$year = (int) $day->format('Y');
		$days = easter_days($year);
		// $days returns the number of days from March 21st until the Easter Sunday, +1 because of Monday
		$holidays[] = date('j.n', strtotime($year . '-03-21 +' . ($days + 1) . ' days'));

		$isWorkDay = true;

		if ($day->format('N') > 5) {
			// Saturday or Sunday
			$isWorkDay = false;
		} elseif (in_array($day->format('j.n'), $holidays)) {
			// Public holiday, hurray!
			$isWorkDay = false;
		}

		return $isWorkDay;
	}
}
