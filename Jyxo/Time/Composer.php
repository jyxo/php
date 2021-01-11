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

use function checkdate;
use function mktime;

/**
 * Time composer used to compose a date/time part by part.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Martin Šamšula
 */
class Composer
{

	/**
	 * Maximal year.
	 */
	public const YEAR_MAX = 2037;

	/**
	 * Minimal year.
	 */
	public const YEAR_MIN = 1902;

	/**
	 * Day.
	 *
	 * @var int
	 */
	private $day = 0;

	/**
	 * Month.
	 *
	 * @var int
	 */
	private $month = 0;

	/**
	 * Year.
	 *
	 * @var int
	 */
	private $year = 0;

	/**
	 * Second.
	 *
	 * @var int
	 */
	private $second = 0;

	/**
	 * Minute.
	 *
	 * @var int
	 */
	private $minute = 0;

	/**
	 * Hour.
	 *
	 * @var int
	 */
	private $hour = 0;

	/**
	 * Returns the composed date/time.
	 *
	 * @return Time
	 */
	public function getTime(): Time
	{
		if ($this->month === 0 || $this->year === 0 || $this->day === 0) {
			throw new ComposerException('Date not complete.', ComposerException::NOT_COMPLETE);
		}

		// Checkdate checks if the provided day is valid. Month and year are validated in their getters.
		// The year is between 1 and 32767 inclusive.
		if (!checkdate($this->month, $this->day, $this->year)) {
			throw new ComposerException('Day out of range.', ComposerException::INVALID);
		}

		$time = mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);

		return new Time($time);
	}

	/**
	 * Sets the day.
	 *
	 * @param int $day Day of the month
	 * @return Composer
	 */
	public function setDay(int $day): self
	{
		if ($day < 1 || $day > 31) {
			throw new ComposerException('Day out of range.', ComposerException::DAY);
		}

		$this->day = $day;

		return $this;
	}

	/**
	 * Sets the month.
	 *
	 * @param int $month Month
	 * @return Composer
	 */
	public function setMonth(int $month): self
	{
		if ($month < 1 || $month > 12) {
			throw new ComposerException('Month out of range.', ComposerException::MONTH);
		}

		$this->month = $month;

		return $this;
	}

	/**
	 * Sets the year.
	 *
	 * @param int $year Year
	 * @return Composer
	 */
	public function setYear(int $year): self
	{
		if ($year > self::YEAR_MAX || $year < self::YEAR_MIN) {
			throw new ComposerException('Year out of range.', ComposerException::YEAR);
		}

		$this->year = $year;

		return $this;
	}

	/**
	 * Sets seconds.
	 *
	 * @param int $second Seconds
	 * @return Composer
	 */
	public function setSecond(int $second): self
	{
		if ($second < 0 || $second > 60) {
			throw new ComposerException('Second out of range.', ComposerException::SECOND);
		}

		$this->second = $second;

		return $this;
	}

	/**
	 * Sets minutes.
	 *
	 * @param int $minute Minutes
	 * @return Composer
	 */
	public function setMinute(int $minute): self
	{
		if ($minute < 0 || $minute > 60) {
			throw new ComposerException('Minute out of range.', ComposerException::MINUTE);
		}

		$this->minute = $minute;

		return $this;
	}

	/**
	 * Sets hours.
	 *
	 * @param int $hour Hours
	 * @return Composer
	 */
	public function setHour(int $hour): self
	{
		if ($hour < 0 || $hour > 24) {
			throw new ComposerException('Hour out of range.', ComposerException::HOUR);
		}

		$this->hour = $hour;

		return $this;
	}

}
