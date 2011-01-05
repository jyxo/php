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
 * Test for \Jyxo\Time\Time class.
 *
 * @see \Jyxo\Time\Time
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 * @author Ondřej Nešpor
 */
class TimeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Prepares the testing environment.
	 */
	protected function setUp()
	{
		bind_textdomain_codeset('messages', 'UTF-8');
		bindtextdomain('messages', DIR_FILES . '/time');
		textdomain('messages');
		putenv('LANG=cs_CZ.UTF-8');
		putenv('LANGUAGE=cs_CZ.UTF-8');
		@setlocale(LC_MESSAGES, 'cs_CZ.UTF-8');
	}

	/**
	 * Tests the constructor.
	 *
	 * @see \Jyxo\Time\Time::__construct()
	 */
	public function testConstruct()
	{
		// Unixtime
		$now = time();
		$time = new Time($now);
		$this->assertEquals(date('Y-m-d', $now), $time->format('Y-m-d'));

		// Strtotime
		$time = new Time('now');
		$this->assertEquals(date('Y-m-d', strtotime('now')), $time->format('Y-m-d'));

		// Invalid strtotime
		try {
			$time = new Time('abcde');
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		// \Jyxo\Time\Time
		$time = new Time(new Time($now));
		$this->assertEquals(date('Y-m-d', $now), $time->format('Y-m-d'));

		$time = new Time(new Time($now), date_default_timezone_get());
		$this->assertEquals(date('Y-m-d', $now), $time->format('Y-m-d'));

		// \DateTime
		$dateTime = \DateTime::createFromFormat('U', $now);

		$time = new Time($dateTime);
		$this->assertEquals($now, $time->format('U'));

		$time = new Time($dateTime, date_default_timezone_get());
		$this->assertEquals(date('Y-m-d', $now), $time->format('Y-m-d'));

		// Invalid parameter
		try {
			$time = new Time(array());
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		try {
			$time = new Time(new \stdClass());
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		try {
			$tmp = new Time($time, new \stdClass());
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		try {
			$tmp = new Time($dateTime, (object)array('foo' => 'bar'));
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}
	}

	/**
	 * Tests the get() method.
	 *
	 * @see \Jyxo\Time\Time::get()
	 */
	public function testGet()
	{
		// Unixtime
		$now = time();
		$this->assertEquals(date('Y-m-d', $now), Time::get($now)->format('Y-m-d'));

		// No need to perform all tests; other possible values were tested in the constructor test
	}

	/**
	 * Tests the now() method.
	 *
	 * @see \Jyxo\Time\Time::now()
	 */
	public function testNow()
	{
		$this->assertEquals(new Time(time()), Time::now());
		$this->assertNotEquals(new Time('+1 day'), Time::now());
	}

	/**
	 * Tests the createFromFormat() method.
	 *
	 * @see \Jyxo\Time\Time::createFromFormat()
	 */
	public function testCreateFromFormat()
	{
		$this->assertEquals(Time::get('2009-12-01')->format('Ym'), Time::createFromFormat('ym', '0912')->format('Ym'));
		$this->assertEquals(Time::get('2009-12-01')->format('Ymd'), Time::createFromFormat('ymd', '091201')->format('Ymd'));
		$this->assertEquals(Time::get('2009-12-01')->format('Ymd'), Time::createFromFormat('Ymd', '20091201')->format('Ymd'));
		$this->assertEquals(Time::get('2009-12-01 12:14:16')->format('Ymd His'), Time::createFromFormat('Y-m-d H:i:s', '2009-12-01 12:14:16')->format('Ymd His'));
	}

	/**
	 * Tests the __get() method.
	 *
	 * @see \Jyxo\Time\Time::__get()
	 */
	public function testMagicGet()
	{
		// Basic types
		$timeZone = new \DateTimeZone('GMT-7');
		$time = new Time('2009-10-10', $timeZone);

		$this->assertEquals('2009-10-10T00:00:00+0700', $time->sql);
		$this->assertEquals('Sat, 10 Oct 09 00:00:00 +0700', $time->email);
		$this->assertEquals('2009-10-10T00:00:00+07:00', $time->web);
		$this->assertEquals('Saturday, 10-Oct-09 00:00:00 GMT-7', $time->cookie);
		$this->assertEquals('Sat, 10 Oct 2009 00:00:00 +0700', $time->rss);
		$this->assertEquals('1255107600', $time->unix);
		$this->assertEquals('Fri, 09 Oct 2009 17:00:00 GMT', $time->http);
		$this->assertEquals(sprintf('10. %s 2009 v 0:00', mb_strtolower(_('October#~Genitive'))), $time->extended);
		$this->assertEquals(sprintf('10. %s 2009 v 0:00', mb_strtolower(_('October#~Genitive'))), $time->full);

		// Interval
		$this->assertEquals(sprintf(ngettext('Day ago', '%s days ago', 5), 5), Time::get('-5 day')->interval);
		$this->assertEquals(sprintf(ngettext('In day', 'In %s days', 5), 5), Time::get('+5 day')->interval);

		// Full
		$this->assertEquals(sprintf(ngettext('Minute ago', '%s minutes ago', 59), 59), Time::get('-59 minutes')->full);
		$this->assertNotEquals(sprintf(ngettext('Minute ago', '%s minutes ago', 60), 60), Time::get('-60 minutes')->full);
		$this->assertEquals(sprintf(ngettext('In minute', 'In %s minutes', 59), 59), Time::get('+59 minutes')->full);
		$this->assertNotEquals(sprintf(ngettext('In minute', 'In %s minutes', 60), 60), Time::get('+60 minutes')->full);

		// Result rounding
		$this->assertEquals(sprintf(ngettext('Minute ago', '%s minutes ago', 1), 1), Time::get('-89 seconds')->full);
		$this->assertEquals(sprintf(ngettext('Minute ago', '%s minutes ago', 2), 2), Time::get('-90 seconds')->full);
		$this->assertEquals(sprintf(ngettext('In minute', 'In %s minutes', 2), 2), Time::get('+149 seconds')->full);
		$this->assertEquals(sprintf(ngettext('In minute', 'In %s minutes', 3), 3), Time::get('+150 seconds')->full);

		// Unknown type
		try {
			$this->assertEquals('', $time->unknown);
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}
	}

	/**
	 * Tests the __call() method.
	 *
	 * @see \Jyxo\Time\Time::__call()
	 */
	public function testCall()
	{
		$time = new Time('-10 days');
		$this->assertEquals($time->unix, $time->getTimestamp());

		$time->modify('+15 days');
		$this->assertEquals(date('Y-m-d', strtotime('+5 days')), $time->format('Y-m-d'));
	}

	/**
	 * Tests the __toString() method and generally Unix timestamp handling.
	 *
	 * @see \Jyxo\Time\Time::__toString()
	 */
	public function testToString()
	{
		ob_start();
		echo Time::get('2002-02-02 02:02:02', 'UTC');
		$output = ob_get_clean();

		$this->assertSame('1012615322', $output);
		$this->assertNotSame(1012615322, $output);

		// Dates with no unix timestamp representation
		// Before 1970
		$output = (string) Time::get('1000-02-02 02:02:02', 'UTC');
		$this->assertSame('', $output);
		// After 2038
		$time = Time::get('3000-02-02 02:02:02', 'UTC');
		$output = (string) $time;
		$this->assertSame('', $output);

		// Move 1000 years back to get a valid unix timestamp
		$time = $time->minus('1000 years');
		$output = (string) $time;
		$this->assertSame('949456922', $output);

		// Set a a different timezone, unix timestamp should be the same (always in UTC)
		$time2 = clone $time;
		$time2->setTimeZone('Indian/Christmas');
		$this->assertSame((string) $time, (string) $time2);

		// Try unix timestamp in different timezones
		$time = Time::get('2001-12-12 21:34:18', 'UTC');
		$time2 = Time::get('2001-12-12 19:34:18', 'GMT+2'); // The time is set back 2 hours to get the same UTC time
		$this->assertSame((string) $time, (string) $time2);

		// Change one timezone and try again
		$time2->setTimeZone('America/Santiago');
		$this->assertSame((string) $time, (string) $time2);
	}

	/**
	 * Tests the format() method.
	 *
	 * @see \Jyxo\Time\Time::format()
	 */
	public function testFormat()
	{
		// Time with no translation necessary
		$this->assertEquals('121212', Time::get('2012-12-12T12:12:12+02:00')->format('ymd'));

		// Weekdays translation
		$days = array(_('Monday'), _('Tuesday'), _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'), _('Sunday'));
		$daysShort = array(_('Mon'), _('Tue'), _('Wed'), _('Thu'), _('Fri'), _('Sat'), _('Sun'));
		foreach ($days as $day => $name) {
			$time = new Time('2009-10-' . ($day + 12));
			$this->assertEquals($days[date('N', strtotime('2009-10-' . ($day + 12))) - 1], $time->format('l'));
			$this->assertEquals($daysShort[date('N', strtotime('2009-10-' . ($day + 12))) - 1], $time->format('D'));
		}

		// Months translation
		$months = array(
			_('January'), _('February'), _('March'), _('April'), _('May'), _('June'), _('July'), _('August'),
			_('September'), _('October'), _('November'), _('December')
		);
		$monthsGen = array(
			_('January#~Genitive'), _('February#~Genitive'), _('March#~Genitive'), _('April#~Genitive'), _('May#~Genitive'),
			_('June#~Genitive'), _('July#~Genitive'), _('August#~Genitive'), _('September#~Genitive'),
			_('October#~Genitive'), _('November#~Genitive'), _('December#~Genitive')
		);
		$monthsShort = array(_('Jan'), _('Feb'), _('Mar'), _('Apr'), _('May#~Shortcut'), _('Jun'), _('Jul'), _('Aug'), _('Sep'), _('Oct'), _('Nov'), _('Dec'));
		foreach ($months as $month => $name) {
			$time = new Time('2009-' . str_pad($month + 1, 2, '0', STR_PAD_LEFT) . '-01');
			$this->assertEquals($name, $time->format('F'));
			$this->assertEquals('1. ' . mb_strtolower($monthsGen[$month]), $time->format('j. F'));
			$this->assertEquals($monthsShort[$month], $time->format('M'));
		}

		// Full date/time
		$this->assertEquals(sprintf('%s 10. %s 2012 10:11:12', $days[5], mb_strtolower($monthsGen[10])), Time::get('2012-11-10 10:11:12')->format('l j. F Y H:i:s'));
		$this->assertEquals(sprintf('%s 2012', $months[9]), Time::get('2012-10-10')->format('F Y'));
		$this->assertEquals(sprintf('%s 2012', $monthsShort[8]), Time::get('2012-09-09')->format('M Y'));

		// Time zone handling
		$time1 = Time::now();
		$timeZone = new \DateTimeZone($time1->getTimeZone()->getName() == 'Europe/Prague' ? 'UTC' : 'Europe/Prague');
		$time2 = Time::now()->setTimeZone($timeZone);

		// Date/times must not be the same (Prague is UTC+1/+2)
		$this->assertNotSame($time1->format('Y-m-d H:i:s'), $time2->format('Y-m-d H:i:s'));

		// Get the results in the same time zone (of the first one)
		$this->assertSame($time1->format('Y-m-d H:i:s'), $time2->format('Y-m-d H:i:s', $time1->getTimeZone()));

		// Get the results in the same time zone (different for both instances)
		$commonTimeZone = new \DateTimeZone(
			$timeZone->getName() == 'Europe/Prague' ? (
				$time1->getTimeZone()->getName() == 'Pacific/Honolulu' ? 'America/Havana' : 'Pacific/Honolulu'
			) : (
				$time1->getTimeZone()->getName() == 'Europe/Prague' ? 'Pacific/Honolulu' : 'Europe/Prague'
			)
		);

		// The "common" time zone differs from both instances' time zones
		$this->assertNotSame($time1->getTimeZone()->getName(), $commonTimeZone->getName());
		$this->assertNotSame($time2->getTimeZone()->getName(), $commonTimeZone->getName());

		// And therefore local times differ (the common time zone is selected so that they do really differ :)
		$this->assertNotSame($time1->format('Y-m-d H:i:s'), $time1->format('Y-m-d H:i:s', $commonTimeZone));
		$this->assertNotSame($time2->format('Y-m-d H:i:s'), $time2->format('Y-m-d H:i:s', $commonTimeZone));

		// The result in the common time zone
		$this->assertSame($time1->format('Y-m-d H:i:s', $commonTimeZone), $time2->format('Y-m-d H:i:s', $commonTimeZone));

		// Invalid timezone - name
		try {
			$this->assertSame(null, $time->format('Y-m-d', 'Foo/Bar'));
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		// Invalid timezone - object
		try {
			$this->assertSame(null, $time->format('Y-m-d', new \stdClass()));
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}
	}

	/**
	 * Tests the formatExtended() method.
	 *
	 * @see \Jyxo\Time\Time::formatExtended()
	 */
	public function testFormatExtended()
	{
		// No parameters provided
		$this->assertEquals(sprintf('6. %s 2008 v 5:04', mb_strtolower(_('July#~Genitive')), _('at')), Time::get('2008-07-06 05:04:03')->formatExtended());
		// Date format set
		$this->assertEquals('08-07-06 v 5:04', Time::get('2008-07-06 05:04:03')->formatExtended('y-m-d'));
		// Both the date and time format set
		$this->assertEquals('08-07-06 v 05:04:03', Time::get('2008-07-06 05:04:03')->formatExtended('y-m-d', 'H:i:s'));
		// Date and time format set; time part in the output should be empty
		$this->assertEquals('2008-07-06', Time::get('2008-07-06 05:04:03')->formatExtended('Y-m-d', ''));

		// Today
		$now = time();
		$this->assertEquals(_('Today'), Time::get($now)->formatExtended('j. F Y', ''));
		$this->assertEquals(_('Today') . ' ' . _('at') . ' ' . date('G:i', $now), Time::get($now, date_default_timezone_get())->formatExtended());

		// Yesterday
		$yesterday = strtotime('-1 day');
		$this->assertEquals(_('Yesterday'), Time::get($yesterday)->formatExtended('j. F Y', ''));
		$this->assertEquals(_('Yesterday') . ' ' . _('at') . ' ' . date('G:i', $yesterday), Time::get($yesterday)->formatExtended());

		// Last week
		$days = array(_('Monday'), _('Tuesday'), _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'), _('Sunday'));
		for ($i = 2; $i < 7; $i++) {
			$day = strtotime('-' . $i . ' days');
			$this->assertEquals($days[date('N', $day) - 1], Time::get($day)->formatExtended('j. F Y', ''));
			$this->assertEquals($days[date('N', $day) - 1] . ' v ' . date('G:i', $day), Time::get($day)->formatExtended());
		}

		// More than a week ago
		$this->assertEquals(sprintf('1. %s 2003', mb_strtolower(_('February#~Genitive'))), Time::get('2003-02-01 04:05:06')->formatExtended('j. F Y', ''));
		$this->assertEquals(sprintf('1. %s 2003 %s 4:05', mb_strtolower(_('February#~Genitive')), _('at')), Time::get('2003-02-01 04:05:06')->formatExtended());

		// Time zone handling
		// Date line
		$time = new Time(gmdate('Y-m-d') . ' 00:00:00', 'UTC');

		$this->assertSame(_('Today'), $time->formatExtended(null, '', 'Europe/Prague'));
		$this->assertSame(_('Yesterday'), $time->minus('2 hour')->formatExtended(null, '', 'Europe/Prague'));

		// Last week
		$days = array(_('Monday'), _('Tuesday'), _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'), _('Sunday'));
		$timestamp = time() - 7 * 86400;
		$time = new Time(gmdate('Y-m-d', $timestamp) . ' 00:00:00', 'UTC');

		$day = $days[date('N', $timestamp) - 1];
		$this->assertSame(sprintf('%s %s %s 05:00:00', $day, gmdate('Y-m-d', $timestamp), _('at')), $time->formatExtended('l Y-m-d', 'H:i:s', 'GMT-5'));

	}

	/**
	 * Tests the formatAsInterval() method.
	 *
	 * @see \Jyxo\Time\Time::formatAsInterval()
	 */
	public function testFormatAsInterval()
	{
		// Now
		$this->assertEquals(_('Now'), Time::now()->formatAsInterval());
		$this->assertEquals(_('Now'), Time::get('-8 seconds')->formatAsInterval());
		$this->assertEquals(_('Now'), Time::get('+8 seconds')->formatAsInterval());
		$this->assertNotEquals(_('Now'), Time::get('-10 seconds')->formatAsInterval());
		$this->assertNotEquals(_('Now'), Time::get('+10 seconds')->formatAsInterval());

		// Most intervals
		foreach (array('minute', 'hour', 'day', 'year') as $period) {
			foreach (array(1, 2) as $count) {
				$this->assertEquals(sprintf(ngettext(sprintf('%s ago', ucfirst($period)), sprintf('%%s %ss ago', $period), $count), $count), Time::get(sprintf('-%s %s', $count, $period))->formatAsInterval());
				$this->assertEquals(sprintf(ngettext(sprintf('%s', ucfirst($period)), sprintf('%%s %ss', $period), $count), $count), Time::get(sprintf('+%s %s', $count, $period))->formatAsInterval(false));
				$this->assertEquals(sprintf(ngettext(sprintf('In %s', $period), sprintf('In %%s %ss', $period), $count), $count), Time::get(sprintf('+%s %s', $count, $period))->formatAsInterval());
			}
		}

		// Months have to be tested separately because of Feb, which is shorter
		foreach (array(1, 2) as $count) {
			try {
				$actual = Time::get(sprintf('-%s month', $count))->formatAsInterval();
				$this->assertEquals(sprintf(ngettext('Month ago', '%s months ago', $count), $count), $actual);
			} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
				$this->assertEquals(sprintf(ngettext('Week ago', '%s weeks ago', 4), 4), $actual);
			}
			try {
				$actual = Time::get(sprintf('+%s month', $count))->formatAsInterval(false);
				$this->assertEquals(sprintf(ngettext('Month', '%s months', $count), $count), $actual);
			} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
				$this->assertEquals(sprintf(ngettext('Week', '%s weeks', 4), 4), $actual);
			}
			try {
				$actual = Time::get(sprintf('+%s month', $count))->formatAsInterval();
				$this->assertEquals(sprintf(ngettext('In month', 'In %s months', $count), $count), $actual);
			} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
				$this->assertEquals(sprintf(ngettext('In week', 'In %s weeks', 4), 4), $actual);
			}
		}

		foreach (array(1, 2) as $count) {
			try {
				$actual = Time::get(sprintf('-%s week', $count))->formatAsInterval();
				$this->assertEquals(sprintf(ngettext('Week ago', '%s weeks ago', $count), $count), $actual);
			} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
				$this->assertEquals(sprintf(ngettext('Day ago', '%s days ago', 7), 7), $actual);
			}
			try {
				$actual = Time::get(sprintf('+%s week', $count))->formatAsInterval(false);
				$this->assertEquals(sprintf(ngettext('Week', '%s weeks', $count), $count), $actual);
			} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
				$this->assertEquals(sprintf(ngettext('Day', '%s days', 7), 7), $actual);
			}
			try {
				$actual = Time::get(sprintf('+%s week', $count))->formatAsInterval();
				$this->assertEquals(sprintf(ngettext('In week', 'In %s weeks', $count), $count), $actual);
			} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
				$this->assertEquals(sprintf(ngettext('In day', 'In %s days', 7), 7), $actual);
			}
		}

		// Seconds have to be tested separately because of "now"
		$this->assertEquals(sprintf(ngettext('Second ago', '%s seconds ago', 20), 20), Time::get('-20 seconds')->formatAsInterval());
		$this->assertEquals(sprintf(ngettext('Second', '%s seconds', 20), 20), Time::get('+20 seconds')->formatAsInterval(false));
		$this->assertEquals(sprintf(ngettext('In second', 'In %s seconds', 20), 20), Time::get('+20 seconds')->formatAsInterval());
	}

	/**
	 * Tests the plus() method.
	 *
	 * @see \Jyxo\Time\Time::plus()
	 */
	public function testPlus()
	{
		// Provided as number of seconds
		$time = Time::get('2005-04-05 00:00:00');
		$this->assertEquals(Time::get('2005-04-05 00:00:10'), $time->plus(10));
		$this->assertEquals(Time::get('2005-04-05 00:01:00'), $time->plus(60));
		$this->assertEquals(Time::get('2005-04-05 02:00:00'), $time->plus(2 * 3600));
		$this->assertEquals(Time::get('2005-04-06 00:00:00'), $time->plus(24 * 3600));
		$this->assertEquals(Time::get('2005-05-05 00:00:00'), $time->plus(30 * 24 * 3600));
		$this->assertEquals(Time::get('2006-04-05 00:00:00'), $time->plus(365 * 24 * 3600));

		// Provided as string
		$this->assertEquals(Time::get('2005-04-06 00:00:00'), $time->plus('1 day'));
		$this->assertEquals(Time::get('2005-06-05 00:00:00'), $time->plus('2 months'));
		$this->assertEquals(Time::get('2008-04-05 00:00:00'), $time->plus('3 year'));

		// Time zone settings
		$timeZone = new \DateTimeZone($time->getTimeZone()->getName() == 'Europe/Prague' ? 'America/Santiago' : 'Europe/Prague');
		$time->setTimeZone($timeZone);

		$time2 = $time->plus(86400);
		$this->assertSame($time->getTimeZone()->getName(), $time2->getTimeZone()->getName());
	}

	/**
	 * Tests the minus() method.
	 *
	 * @see \Jyxo\Time\Time::minus()
	 */
	public function testMinus()
	{
		// Provided as number of seconds
		$time = Time::get('2005-05-05 00:00:00');
		$this->assertEquals(Time::get('2005-05-04 23:59:50'), $time->minus(10));
		$this->assertEquals(Time::get('2005-05-04 23:59:00'), $time->minus(60));
		$this->assertEquals(Time::get('2005-05-04 22:00:00'), $time->minus(2 * 3600));
		$this->assertEquals(Time::get('2005-05-04 00:00:00'), $time->minus(24 * 3600));
		$this->assertEquals(Time::get('2005-04-05 00:00:00'), $time->minus(30 * 24 * 3600));
		$this->assertEquals(Time::get('2004-05-05 00:00:00'), $time->minus(365 * 24 * 3600));

		// Provided as string
		$this->assertEquals(Time::get('2005-05-04 00:00:00'), $time->minus('1 day'));
		$this->assertEquals(Time::get('2005-03-05 00:00:00'), $time->minus('2 months'));
		$this->assertEquals(Time::get('2002-05-05 00:00:00'), $time->minus('3 year'));

		// Time zone settings
		$timeZone = new \DateTimeZone($time->getTimeZone()->getName() == 'Europe/Prague' ? 'America/Santiago' : 'Europe/Prague');
		$time->setTimeZone($timeZone);

		$time2 = $time->minus(86400);
		$this->assertSame($time->getTimeZone()->getName(), $time2->getTimeZone()->getName());
	}

	/**
	 * Tests the hasHappened() method.
	 *
	 * @see \Jyxo\Time\Time::hasHappened()
	 */
	public function testHasHappened()
	{
		$this->assertTrue(Time::get('-1 day')->hasHappened());
		$this->assertTrue(Time::get('-5 second')->hasHappened());
		$this->assertFalse(Time::get('+5 second')->hasHappened());
		$this->assertFalse(Time::get('+1 day')->hasHappened());
	}

	/**
	 * Tests the truncate() method.
	 *
	 * @see \Jyxo\Time\Time::truncate()
	 */
	public function testTruncate()
	{
		// Unknown unit
		try {
			$time = Time::now()->truncate('unknown');
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		$tests = array(
			Time::SECOND => '2004-05-06 07:08:09',
			Time::MINUTE => '2004-05-06 07:08:00',
			Time::HOUR => '2004-05-06 07:00:00',
			Time::DAY => '2004-05-06 00:00:00',
			Time::MONTH => '2004-05-01 00:00:00',
			Time::YEAR => '2004-01-01 00:00:00'
		);

		$time = new Time('2004-05-06 07:08:09');
		foreach ($tests as $unit => $expected) {
			$this->assertEquals(Time::get($expected), $time->truncate($unit));
		}
	}

	/**
	 * Tests the getTimezone() method.
	 *
	 * @see \Jyxo\Time\Time::getTimezone()
	 */
	public function testGetTimezone()
	{
		// Explicit time zone definition
		$time = Time::get(time(), 'UTC');
		$this->assertSame('UTC', $time->getTimeZone()->getName());

		// Default time zone
		$time = Time::now();
		$this->assertSame(date_default_timezone_get(), $time->getTimeZone()->getName());
	}

	/**
	 * Tests the setTimezone() method.
	 *
	 * @see \Jyxo\Time\Time::setTimezone()
	 */
	public function testSetTimeZone()
	{
		$time = Time::get(time(), 'UTC');
		$this->assertNotSame('Europe/Prague', $time->getTimeZone()->getName());

		$time->setTimeZone('Europe/Prague');
		$this->assertSame('Europe/Prague', $time->getTimeZone()->getName());

		$timeZone = new \DateTimeZone('America/Santiago');
		$time->setTimeZone($timeZone);
		$this->assertSame('America/Santiago', $time->getTimeZone()->getName());

		// Invalid timezone
		try {
			$time->setTimeZone('Foo/Bar');
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}
	}

	/**
	 * Tests the serialize() function.
	 *
	 * @see \Jyxo\Time\Time::serialize()
	 */
	public function testSerialize()
	{
		$time = time();
		$timeZone = new \DateTimeZone(date_default_timezone_get());
		$timeInstance = new Time($time, $timeZone);

		$this->assertSame(
			'C:14:"Jyxo\Time\Time":' . (20 + strlen(date_default_timezone_get())) .':{' . date('Y-m-d H:i:s', $time) . ' ' . date_default_timezone_get() .'}',
			serialize($timeInstance)
		);
	}

	/**
	 * Tests the serialize() function.
	 *
	 * @see \Jyxo\Time\Time::unserialize()
	 */
	public function testUnserialize()
	{
		$time = time();
		$serialized = 'C:14:"Jyxo\Time\Time":33:{' . date('Y-m-d H:i:s', $time) . ' Europe/Prague}';
		$unserialized = @unserialize($serialized);

		$this->assertSame((string) $time, (string) $unserialized);
		$this->assertSame('Europe/Prague', $unserialized->getTimezone()->getName());

		// Invalid serialized data
		$serialized = 'C:14:"Jyxo\Time\Time":3:{foo}';
		try {
			$unserialized = @unserialize($serialized);
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		// Invalid timezone
		$serialized = 'C:14:"Jyxo\Time\Time":27:{2010-12-12 10:00:00 Foo/Bar}';
		try {
			$unserialized = @unserialize($serialized);
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		// Invalid date/time definition
		$serialized = 'C:14:"Jyxo\Time\Time":33:{2010-13-12 10:00:00 Europe/Prague}';
		try {
			$unserialized = @unserialize($serialized);
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		// Offset time zone definition
		$serialized = 'C:14:"Jyxo\Time\Time":26:{' . date('Y-m-d H:i:s', $time) . ' +05:30}';
		$unserialized = @unserialize($serialized);
		$this->assertEquals('+05:30', $unserialized->getTimeZone()->getName());

		$serialized = 'C:14:"Jyxo\Time\Time":26:{' . date('Y-m-d H:i:s', $time) . ' -12:00}';
		$unserialized = @unserialize($serialized);
		$this->assertEquals('-12:00', $unserialized->getTimeZone()->getName());

		// PHP bug http://bugs.php.net/bug.php?id=45528 test
		try {
			$tz = new \DateTimeZone($unserialized->getTimeZone()->getName());
			$this->fail('\Exception expected');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\Exception', $e);
		}

		// Invalid time zone offset
		$serialized = 'C:14:"Jyxo\Time\Time":26:{' . date('Y-m-d H:i:s', $time) . ' +05:60}';
		try {
			$unserialized = @unserialize($serialized);
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}

		// Invalid time zone offset
		$serialized = 'C:14:"Jyxo\Time\Time":26:{' . date('Y-m-d H:i:s', $time) . ' -13:00}';
		try {
			$unserialized = @unserialize($serialized);
			$this->fail('Expected exception \InvalidArgumentException.');
		} catch (\Exception $e) {
			// Correctly thrown exception
			$this->assertInstanceOf('\InvalidArgumentException', $e);
		}


	}

}
