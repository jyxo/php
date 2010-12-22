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

namespace Jyxo\Time;

/**
 * Time composer used to compose a date/time part by part.
 *
 * @category Jyxo
 * @package Jyxo\Time
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Martin Šamšula
 */
class Composer
{
	/**
	 * Maximal year.
	 *
	 * @var integer
	 */
	const YEAR_MAX = 2037;

	/**
	 * Minimal year.
	 *
	 * @var integer
	 */
	const YEAR_MIN = 1902;

	/**
	 * Day.
	 *
	 * @var integer
	 */
	private $day = 0;

	/**
	 * Month.
	 *
	 * @var integer
	 */
	private $month = 0;

	/**
	 * Year.
	 *
	 * @var integer
	 */
	private $year = 0;

	/**
	 * Second.
	 *
	 * @var integer
	 */
	private $second = 0;

	/**
	 * Minute.
	 *
	 * @var integer
	 */
	private $minute = 0;

	/**
	 * Hour.
	 *
	 * @var integer
	 */
	private $hour = 0;

	/**
	 * Returns the composed date/time.
	 *
	 * @return \Jyxo\Time\Time
	 * @throws \Jyxo\Time\ComposerException If the date is incomplete or invalid
	 */
	public function getTime()
	{
		if ($this->month === 0 || $this->year === 0 || $this->day === 0) {
			throw new \Jyxo\Time\ComposerException('Date not complete.', \Jyxo\Time\ComposerException::NOT_COMPLETE);
		}

		// Checkdate checks if the provided day is valid. Month and year are validated in their getters.
		// The year is between 1 and 32767 inclusive.
		if (!checkdate($this->month, $this->day, $this->year)) {
			throw new \Jyxo\Time\ComposerException('Day out of range.', \Jyxo\Time\ComposerException::INVALID);
		}

		$time = mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
		return new \Jyxo\Time\Time($time);
	}

	/**
	 * Sets the day.
	 *
	 * @param integer $day Day of the month
	 * @return \Jyxo\Time\Composer
	 * @throws \Jyxo\Time\ComposerException If the provided day is invalid
	 */
	public function setDay($day)
	{
		$day = (integer) $day;

		if ($day < 1 || $day > 31) {
			throw new \Jyxo\Time\ComposerException('Day out of range.', \Jyxo\Time\ComposerException::DAY);
		}

		$this->day = $day;

		return $this;
	}

	/**
	 * Sets the month.
	 *
	 * @param integer $month Month
	 * @return \Jyxo\Time\Composer
	 * @throws \Jyxo\Time\ComposerException If the month is invalid.
	 */
	public function setMonth($month)
	{
		$month = (integer) $month;

		if ($month < 1 || $month > 12) {
			throw new \Jyxo\Time\ComposerException('Month out of range.', \Jyxo\Time\ComposerException::MONTH);
		}

		$this->month = $month;

		return $this;
	}

	/**
	 * Sets the year.
	 *
	 * @param integer $year Year
	 * @return \Jyxo\Time\Composer
	 * @throws \Jyxo\Time\ComposerException If the year is invalid.
	 */
	public function setYear($year)
	{
		$year = (integer) $year;

		if ($year > self::YEAR_MAX || $year < self::YEAR_MIN) {
			throw new \Jyxo\Time\ComposerException('Year out of range.', \Jyxo\Time\ComposerException::YEAR);
		}

		$this->year = $year;

		return $this;
	}

	/**
	 * Sets seconds.
	 *
	 * @param integer $second Seconds
	 * @return \Jyxo\Time\Composer
	 * @throws \Jyxo\Time\ComposerException If seconds are invalid.
	 */
	public function setSecond($second)
	{
		$second = (integer) $second;

		if ($second < 0 || $second > 60) {
			throw new \Jyxo\Time\ComposerException('Second out of range.', \Jyxo\Time\ComposerException::SECOND);
		}

		$this->second = $second;

		return $this;
	}

	/**
	 * Sets minutes.
	 *
	 * @param integer $minute Minutes
	 * @return \Jyxo\Time\Composer
	 * @throws \Jyxo\Time\ComposerException If minutes are invalid.
	 */
	public function setMinute($minute)
	{
		$minute = (integer) $minute;

		if ($minute < 0 || $minute > 60) {
			throw new \Jyxo\Time\ComposerException('Minute out of range.', \Jyxo\Time\ComposerException::MINUTE);
		}

		$this->minute = $minute;

		return $this;
	}

	/**
	 * Sets hours.
	 *
	 * @param integer $hour Hours
	 * @return \Jyxo\Time\Composer
	 * @throws \Jyxo\Time\ComposerException If hours are invalid.
	 */
	public function setHour($hour)
	{
		$hour = (integer) $hour;

		if ($hour < 0 || $hour > 24) {
			throw new \Jyxo\Time\ComposerException('Hour out of range.', \Jyxo\Time\ComposerException::HOUR);
		}

		$this->hour = $hour;

		return $this;
	}
}
