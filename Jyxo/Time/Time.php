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

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Serializable;
use Throwable;
use function _;
use function array_combine;
use function array_keys;
use function call_user_func_array;
use function count;
use function date_default_timezone_get;
use function explode;
use function intval;
use function is_numeric;
use function is_object;
use function is_string;
use function mb_strtolower;
use function ngettext;
use function preg_match;
use function preg_replace;
use function round;
use function sprintf;
use function strpos;
use function strtr;
use function time;
use function vsprintf;

/**
 * Class for working with date and time.
 * Internally uses a \DateTime object.
 * Initialization is possible using almost any date/time format (unix timestamp, SQL form, ...).
 *
 * Requires the Gettext PHP extension or any other implementation of the _(string) translation function.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 * @author Jan Kolibač
 * @author Roman Řáha
 * @author Martin Šamšula
 * @author Ondřej Nešpor
 */
class Time implements Serializable
{

	/**
	 * Second.
	 */
	public const SECOND = 'second';

	/**
	 * Minute.
	 */
	public const MINUTE = 'minute';

	/**
	 * Hour.
	 */
	public const HOUR = 'hour';

	/**
	 * Day.
	 */
	public const DAY = 'day';

	/**
	 * Week.
	 */
	public const WEEK = 'week';

	/**
	 * Month.
	 */
	public const MONTH = 'month';

	/**
	 * Year.
	 */
	public const YEAR = 'year';

	/**
	 * Number of seconds in a second.
	 */
	public const INTERVAL_SECOND = 1;

	/**
	 * Number of seconds in a minute.
	 */
	public const INTERVAL_MINUTE = 60;

	/**
	 * Number of seconds in an hour.
	 */
	public const INTERVAL_HOUR = 3600;

	/**
	 * Number of seconds in a day.
	 */
	public const INTERVAL_DAY = 86400;

	/**
	 * Number of seconds in a week.
	 */
	public const INTERVAL_WEEK = 604800;

	/**
	 * Number of seconds in a month.
	 */
	public const INTERVAL_MONTH = 2592000;

	/**
	 * Number of seconds in a year.
	 */
	public const INTERVAL_YEAR = 31536000;

	/**
	 * \DateTime instance.
	 *
	 * @var DateTime
	 */
	private $dateTime;

	/**
	 * \DateTimeZone instance of the original timezone.
	 *
	 * @var DateTimeZone
	 */
	private $originalTimeZone;

	/**
	 * Constructor.
	 *
	 * Creates an instance and initializes the internal date/time representation.
	 *
	 * @param string|int|Time|DateTime $time Date/time definition
	 * @param string|DateTimeZone $timeZone Time zone definition
	 */
	public function __construct($time, $timeZone = null)
	{
		if (!is_object($time)) {
			$timeZone = $this->createTimeZone($timeZone ?: date_default_timezone_get());

			if (is_numeric($time)) {
				// Unix timestamp as an integer or string
				$this->dateTime = new DateTime('', $timeZone);
				$this->dateTime->setTimestamp($time);
			} elseif (is_string($time)) {
				// Textual representation
				try {
					$this->dateTime = new DateTime($time, $timeZone);
				} catch (Throwable $e) {
					throw new InvalidArgumentException(sprintf('Provided textual date/time definition "%s" is invalid', $time), 0, $e);
				}
			} else {
				throw new InvalidArgumentException(
					sprintf(
						'Provided date/time must be a number, %s or %s instance or a parameter compatible with PHP function strtotime().',
						self::class,
						DateTimeInterface::class
					)
				);
			}
		} elseif ($time instanceof self) {
			// \Jyxo\Time\Time
			$this->dateTime = new DateTime($time->format('Y-m-d H:i:s'), $time->getTimeZone());

			if ($timeZone) {
				$this->dateTime->setTimezone($this->createTimeZone($timeZone));
			}
		} elseif ($time instanceof DateTime) {
			// \DateTime
			$this->dateTime = clone $time;

			if ($timeZone) {
				$this->dateTime->setTimezone($this->createTimeZone($timeZone));
			}
		} else {
			throw new InvalidArgumentException(
				sprintf(
					'Provided date/time must be a number, %s or %s instance or a parameter compatible with PHP function strtotime().',
					self::class,
					DateTimeInterface::class
				)
			);
		}
	}

	/**
	 * Helper function for creating an instance with the given date/time.
	 *
	 * Useful for one-time usage.
	 *
	 * @param string|int|Time|DateTime $time Date/time definition
	 * @param string|DateTimeZone $timeZone Time zone definition
	 * @return Time
	 */
	public static function get($time, $timeZone = null): self
	{
		return new self($time, $timeZone);
	}

	/**
	 * Returns an instance with the current date/time.
	 *
	 * @return Time
	 */
	public static function now(): self
	{
		return new self(time());
	}

	/**
	 * Creates an instance using a date/time definition in the given format.
	 *
	 * @param string $format Date/time format
	 * @param string $time Date/time definition
	 * @return Time
	 */
	public static function createFromFormat(string $format, string $time): self
	{
		return new self(DateTime::createFromFormat($format, $time));
	}

	/**
	 * Returns the actual time zone.
	 *
	 * @return DateTimeZone
	 */
	public function getTimeZone(): DateTimeZone
	{
		return $this->dateTime->getTimezone();
	}

	/**
	 * Sets a new time zone.
	 *
	 * @param string|DateTimeZone $timeZone The new time zone
	 * @return Time
	 */
	public function setTimeZone($timeZone): self
	{
		$this->dateTime->setTimezone($this->createTimeZone($timeZone));

		return $this;
	}

	/**
	 * Returns date/time in the given format with months and days translated.
	 *
	 * @param string $format Requested format
	 * @param string|DateTimeZone $timeZone Result time zone definition
	 * @return string
	 */
	public function format(string $format, $timeZone = null): string
	{
		// Prepares the right result time zone if needed
		if ($timeZone) {
			$this->setTemporaryTimeZone($timeZone);
		}

		// Translation required?
		if (preg_match('~(?:^|[^\\\])[lDFM]~', $format)) {
			static $days = [];

			if (empty($days)) {
				$days = [_('Monday'), _('Tuesday'), _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'), _('Sunday')];
			}

			static $daysShort = [];

			if (empty($daysShort)) {
				$daysShort = [_('Mon'), _('Tue'), _('Wed'), _('Thu'), _('Fri'), _('Sat'), _('Sun')];
			}

			static $months = [];

			if (empty($months)) {
				$months = [
					_('January'),
					_('February'),
					_('March'),
					_('April'),
					_('May'),
					_('June'),
					_('July'),
					_('August'),
					_('September'),
					_('October'),
					_('November'),
					_('December'),
				];
			}

			static $monthsGen = [];

			if (empty($monthsGen)) {
				$monthsGen = [
					_('January#~Genitive'),
					_('February#~Genitive'),
					_('March#~Genitive'),
					_('April#~Genitive'),
					_('May#~Genitive'),
					_('June#~Genitive'),
					_('July#~Genitive'),
					_('August#~Genitive'),
					_('September#~Genitive'),
					_('October#~Genitive'),
					_('November#~Genitive'),
					_('December#~Genitive'),
				];
			}

			static $monthsShort = [];

			if (empty($monthsShort)) {
				$monthsShort = [
					_('Jan'),
					_('Feb'),
					_('Mar'),
					_('Apr'),
					_('May#~Shortcut'),
					_('Jun'),
					_('Jul'),
					_('Aug'),
					_('Sep'),
					_('Oct'),
					_('Nov'), _('Dec')];
			}

			// Replace certain identifiers with fake ones
			$search = ['~(^|[^\\\])l~', '~(^|[^\\\])D~', '~(^|[^\\\])F~', '~(^|[^\\\])M~'];
			$replace = ['$1<===>', '$1<___>', '$1<--->', '$1<...>'];
			$format = preg_replace($search, $replace, $format);

			// Format the rest of the date
			$date = $this->dateTime->format($format);

			// Calculate day and month
			$day = $this->dateTime->format('N') - 1;
			$month = $this->dateTime->format('n') - 1;

			// If the month is not at the beginning, the genitive case and lowercase will be used
			$monthName = strpos($format, '<--->') !== 0 ? mb_strtolower($monthsGen[$month], 'utf-8') : $months[$month];

			// Add translated days and months into the result
			$result = strtr(
				$date,
				[
					'<===>' => $days[$day],
					'<___>' => $daysShort[$day],
					'<--->' => $monthName,
					'<...>' => $monthsShort[$month],
				]
			);
		} else {
			// No need to translate
			$result = $this->dateTime->format($format);
		}

		// If a custom result timezone was specified, revert the original one
		if ($timeZone) {
			$this->revertOriginalTimeZone();
		}

		return $result;
	}

	/**
	 * Returns date/time in the form of:
	 *
	 * Today at 10:00
	 * Yesterday at 10:00
	 * Friday at 10:00
	 * 21. March 2009 at 10:00
	 *
	 * @param string $dateFormat Date format
	 * @param string $timeFormat Time format
	 * @param string|DateTimeZone $timeZone Result time zone definition
	 * @return string
	 */
	public function formatExtended(string $dateFormat = 'j. F Y', string $timeFormat = 'G:i', $timeZone = null): string
	{
		// Sets a custom result time zone if needed
		if ($timeZone) {
			$this->setTemporaryTimeZone($timeZone);
		}

		if (
			($this->dateTime < new DateTime('midnight - 6 days', $this->dateTime->getTimezone()))
			|| ($this->dateTime >= new DateTime(
				'midnight + 24 hours',
				$this->dateTime->getTimezone()
			))
		) {
			// Past and future dates
			$date = $this->format($dateFormat);
		} elseif ($this->dateTime >= new DateTime('midnight', $this->dateTime->getTimezone())) {
			// Today
			$date = _('Today');
		} elseif ($this->dateTime >= new DateTime('midnight - 24 hours', $this->dateTime->getTimezone())) {
			// Yesterday
			$date = _('Yesterday');
		} else {
			// Last week
			static $days = [];

			if (empty($days)) {
				$days = [_('Monday'), _('Tuesday'), _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'), _('Sunday')];
			}

			$date = $days[$this->dateTime->format('N') - 1];
		}

		$result = empty($timeFormat) ? $date : $date . ' ' . _('at') . ' ' . $this->dateTime->format($timeFormat);

		// If a custom result timezone was specified, revert the original one
		if ($timeZone) {
			$this->revertOriginalTimeZone();
		}

		return $result;
	}

	/**
	 * Function for formatting time differences into human readable forms.
	 *
	 * $t < 10 seconds = Now
	 * 10 seconds <= $t < 60 seconds
	 * 1 minute <= $t < 1 hour
	 * 1 hour <= $t < 24 hours
	 * 1 day <= $t < 7 days
	 * 1 week <= $t < 4 weeks
	 * 1 month <= $t < 12 months
	 * 1 year <= $t < n years
	 *
	 * @param bool $useTense Defines if declension should be used
	 * @param string|DateTimeZone $timeZone Result time zone definition
	 * @return string
	 */
	public function formatAsInterval(bool $useTense = true, $timeZone = null): string
	{
		static $intervalList = [
			self::YEAR => self::INTERVAL_YEAR,
			self::MONTH => self::INTERVAL_MONTH,
			self::WEEK => self::INTERVAL_WEEK,
			self::DAY => self::INTERVAL_DAY,
			self::HOUR => self::INTERVAL_HOUR,
			self::MINUTE => self::INTERVAL_MINUTE,
			self::SECOND => self::INTERVAL_SECOND,
		];

		// Comparison time zone
		$timeZone = $timeZone ? $this->createTimeZone($timeZone) : $this->dateTime->getTimezone();

		// Difference between the stored date/time and now
		$differenceObject = $this->dateTime->diff(new DateTime('', $timeZone));
		$diffArray = array_combine(
			array_keys($intervalList),
			explode('-', $differenceObject->format('%y-%m-0-%d-%h-%i-%s'))
		);

		// Compute the difference in seconds
		$diff = 0;

		foreach ($diffArray as $interval => $intervalCount) {
			$diff += $intervalList[$interval] * $intervalCount;
		}

		// If the difference is less than 10 seconds, "now" is returned
		if ($diff < 10) {
			return _('Now');
		}

		// Find the appropriate unit and calculate number of units
		foreach ($intervalList as $interval => $seconds) {
			if ($seconds <= $diff) {
				$num = (int) round($diff / $seconds);

				break;
			}
		}

		// Past or future
		$period = $differenceObject->format('%R') === '+' ? 'past' : 'future';

		// Dictionary - this part could be written shorter but this implementation is faster
		$tense = $useTense ? $period : 'infinitive';

		switch ($tense) {
			// Past
			case 'past':
				switch ($interval) {
					case self::YEAR:
						return sprintf(ngettext('Year ago', '%s years ago', $num), $num);
					case self::MONTH:
						return sprintf(ngettext('Month ago', '%s months ago', $num), $num);
					case self::WEEK:
						return sprintf(ngettext('Week ago', '%s weeks ago', $num), $num);
					case self::DAY:
						return sprintf(ngettext('Day ago', '%s days ago', $num), $num);
					case self::HOUR:
						return sprintf(ngettext('Hour ago', '%s hours ago', $num), $num);
					case self::MINUTE:
						return sprintf(ngettext('Minute ago', '%s minutes ago', $num), $num);
					case self::SECOND:
					default:
						return sprintf(ngettext('Second ago', '%s seconds ago', $num), $num);
				}

				break;

			// Future
			case 'future':
				switch ($interval) {
					case self::YEAR:
						return sprintf(ngettext('In year', 'In %s years', $num), $num);
					case self::MONTH:
						return sprintf(ngettext('In month', 'In %s months', $num), $num);
					case self::WEEK:
						return sprintf(ngettext('In week', 'In %s weeks', $num), $num);
					case self::DAY:
						return sprintf(ngettext('In day', 'In %s days', $num), $num);
					case self::HOUR:
						return sprintf(ngettext('In hour', 'In %s hours', $num), $num);
					case self::MINUTE:
						return sprintf(ngettext('In minute', 'In %s minutes', $num), $num);
					case self::SECOND:
					default:
						return sprintf(ngettext('In second', 'In %s seconds', $num), $num);
				}

				break;

			// Infinitive
			case 'infinitive':
				switch ($interval) {
					case self::YEAR:
						return sprintf(ngettext('Year', '%s years', $num), $num);
					case self::MONTH:
						return sprintf(ngettext('Month', '%s months', $num), $num);
					case self::WEEK:
						return sprintf(ngettext('Week', '%s weeks', $num), $num);
					case self::DAY:
						return sprintf(ngettext('Day', '%s days', $num), $num);
					case self::HOUR:
						return sprintf(ngettext('Hour', '%s hours', $num), $num);
					case self::MINUTE:
						return sprintf(ngettext('Minute', '%s minutes', $num), $num);
					case self::SECOND:
					default:
						return sprintf(ngettext('Second', '%s seconds', $num), $num);
				}

				break;
			default:
				break;
		}
	}

	/**
	 * Returns a new date/time object and adds with the given interval added.
	 *
	 * @param int|string $interval Number of seconds or a string compatible with the strtotime() function
	 * @return Time
	 */
	public function plus($interval): self
	{
		if (is_numeric($interval)) {
			$interval .= ' seconds';
		}

		$dateTime = clone $this->dateTime;
		$dateTime->modify('+' . (string) $interval);

		return new self($dateTime);
	}

	/**
	 * Returns a new date/time object and adds with the given interval subtracted.
	 *
	 * @param int|string $interval Number of seconds or a string compatible with the strtotime() function
	 * @return Time
	 */
	public function minus($interval): self
	{
		if (is_numeric($interval)) {
			$interval .= ' seconds';
		}

		$dateTime = clone $this->dateTime;
		$dateTime->modify('-' . (string) $interval);

		return new self($dateTime);
	}

	/**
	 * Checks if the date/time already happened.
	 *
	 * Compares the internal date/time with the current local time of the appropriate time zone.
	 *
	 * @return bool
	 */
	public function hasHappened(): bool
	{
		return $this->dateTime->diff(DateTime::createFromFormat('U', (string) time(), $this->dateTime->getTimezone()))->format(
			'%R'
		) === '+';
	}

	/**
	 * Returns a new instance with date/time truncated to the given unit.
	 *
	 * @param string $unit Unit to truncate the date/time to
	 * @return Time
	 */
	public function truncate(string $unit): self
	{
		$dateTime = [
			self::YEAR => 0,
			self::MONTH => 1,
			self::DAY => 1,
			self::HOUR => 0,
			self::MINUTE => 0,
			self::SECOND => 0,
		];

		switch ((string) $unit) {
			case self::SECOND:
				$dateTime[self::SECOND] = $this->dateTime->format('s');

				// Intentionally missing break

			case self::MINUTE:
				$dateTime[self::MINUTE] = $this->dateTime->format('i');

				// Intentionally missing break

			case self::HOUR:
				$dateTime[self::HOUR] = $this->dateTime->format('H');

				// Intentionally missing break

			case self::DAY:
				$dateTime[self::DAY] = $this->dateTime->format('d');

				// Intentionally missing break

			case self::MONTH:
				$dateTime[self::MONTH] = $this->dateTime->format('m');

				// Intentionally missing break

			case self::YEAR:
				$dateTime[self::YEAR] = $this->dateTime->format('Y');

				break;
			default:
				throw new InvalidArgumentException(sprintf('Time unit %s is not defined.', $unit));
		}

		return new self(vsprintf('%s-%s-%sT%s:%s:%s', $dateTime), $this->dateTime->getTimezone());
	}

	/**
	 * Object serialization.
	 *
	 * @return string
	 */
	public function serialize(): string
	{
		return $this->dateTime->format('Y-m-d H:i:s ') . $this->dateTime->getTimezone()->getName();
	}

	/**
	 * Object deserialization.
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
	 * @param string $serialized Serialized data
	 */
	public function unserialize($serialized): void
	{
		try {
			$data = explode(' ', $serialized);

			if (count($data) !== 3) {
				throw new Exception('Serialized data have to be in the "Y-m-d H:i:s TimeZone" format');
			}

			if (preg_match('~([+-]\\d{2}):?([\\d]{2})~', $data[2], $matches)) {
				// Timezone defined by an UTC offset

				if (
					$matches[2] < 0
					|| $matches[2] > 59
					|| intval($matches[1] . $matches[2]) < -1200
					|| intval($matches[1] . $matches[2]) > 1200
				) {
					// Invalid offset - minutes part is invalid or the whole offset is not <= 12:00 and >= -12:00
					throw new Exception(sprintf('Invalid time zone UTC offset definition: %s', $matches[0]));
				}

				$data[1] .= ' ' . $matches[1] . $matches[2];
				$this->dateTime = new DateTime($data[0] . ' ' . $data[1]);
			} else {
				$this->dateTime = new DateTime($data[0] . ' ' . $data[1], $this->createTimeZone($data[2]));
			}
		} catch (Throwable $e) {
			throw new InvalidArgumentException('Deserialization error', 0, $e);
		}
	}

	/**
	 * Creates a \DateTimeZone object from a time zone definition
	 *
	 * @param string|DateTimeZone $definition Time zone definition
	 * @return DateTimeZone
	 */
	protected function createTimeZone($definition): DateTimeZone
	{
		if (is_string($definition)) {
			try {
				return new DateTimeZone($definition);
			} catch (Throwable $e) {
				throw new InvalidArgumentException(sprintf('Invalid timezone definition "%s"', $definition), 0, $e);
			}
		} elseif (!$definition instanceof DateTimeZone) {
			throw new InvalidArgumentException('Invalid timezone definition');
		}

		return $definition;
	}

	/**
	 * Sets a time zone temporarily.
	 *
	 * @param string|DateTimeZone $timeZone Temporary time zone definition
	 */
	protected function setTemporaryTimeZone($timeZone): void
	{
		$this->originalTimeZone = $this->dateTime->getTimezone();

		try {
			$this->setTimeZone($this->createTimeZone($timeZone));
		} catch (InvalidArgumentException $e) {
			$this->originalTimeZone = null;

			throw $e;
		}
	}

	/**
	 * Reverts the original time zone.
	 *
	 * @return Time
	 */
	protected function revertOriginalTimeZone(): self
	{
		if ($this->originalTimeZone !== null) {
			$this->dateTime->setTimezone($this->originalTimeZone);
			$this->originalTimeZone = null;
		}

		return $this;
	}

	/**
	 * Returns date/time in the requested format.
	 *
	 * @param string $name Format name
	 * @return mixed
	 */
	public function __get(string $name)
	{
		switch ($name) {
			case 'sql':
				return $this->dateTime->format(DateTime::ISO8601);
			case 'email':
				return $this->dateTime->format(DateTime::RFC822);
			case 'web':
				return $this->dateTime->format(DateTime::W3C);
			case 'cookie':
				return $this->dateTime->format(DateTime::COOKIE);
			case 'rss':
				return $this->dateTime->format(DateTime::RSS);
			case 'unix':
				// Returns false if the stored date/time has no valid unix timestamp representation
				return $this->dateTime->getTimestamp();
			case 'http':
				$this->setTemporaryTimeZone('GMT');
				$result = $this->dateTime->format('D, d M Y H:i:s') . ' GMT';
				$this->revertOriginalTimeZone();

				return $result;
			case 'extended':
				return $this->formatExtended();
			case 'interval':
				return $this->formatAsInterval();
			case 'full':
				// If the difference between now and the stored date/time if greater than one hour
				return (int) $this->dateTime->diff(new DateTime())->format('%y%m%d%h') > 0
					? $this->formatExtended()
					: $this->formatAsInterval();

			default:
				throw new InvalidArgumentException(sprintf('Unknown format %s.', $name));
		}
	}

	/**
	 * Calls a method directly on the internal \DateTime object.
	 *
	 * @param string $method Method name
	 * @param array $args Method arguments
	 * @return mixed
	 */
	public function __call(string $method, array $args)
	{
		return call_user_func_array([$this->dateTime, $method], $args);
	}

	/**
	 * Returns date/time in the unix timestamp format.
	 *
	 * @return string Returns empty string if the stored date/time has no valid UT representation
	 */
	public function __toString(): string
	{
		return (string) $this->dateTime->getTimestamp();
	}

}
