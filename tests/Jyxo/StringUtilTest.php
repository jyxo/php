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

namespace Jyxo;

require_once __DIR__ . '/../bootstrap.php';

/**
 * String processing test.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 * @author Jaroslav Hanslík
 * @author Ondřej Nešpor
 */
class StringUtilTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests string trimming.
	 */
	public function testCut()
	{
		// Trim on space
		$this->assertEquals('žluťoučký kůň...', $this->checkStringCut('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		// Trim on period
		$this->assertEquals('žluťoučký kůň...', $this->checkStringCut('žluťoučký kůň.Příšerně úpěl ďábelské ódy'));
		// Trim on period and space
		$this->assertEquals('žluťoučký kůň...', $this->checkStringCut('žluťoučký kůň. Příšerně úpěl ďábelské ódy'));
		// Trim on comma
		$this->assertEquals('žluťoučký kůň...', $this->checkStringCut('žluťoučký kůň,příšerně úpěl ďábelské ódy'));
		// Trim on semicolon
		$this->assertEquals('žluťoučký kůň...', $this->checkStringCut('žluťoučký kůň;příšerně úpěl ďábelské ódy'));

		// Word boundary just at the end
		$this->assertEquals('abcdefghijklm...', $this->checkStringCut('abcdefghijklmno pqrst'));
		$this->assertEquals('abcdefghijklm...', $this->checkStringCut('abcdefghijklmn opqrst'));
		$this->assertEquals('abcdefghijklm...', $this->checkStringCut('abcdefghijklm nopqrst'));

		// No word boundaries
		$this->assertEquals('abcdefghijklm...', $this->checkStringCut('abcdefghijklmnopqrstuvwxyz'));

		// Etc as HTML entity
		$this->assertEquals('žluťoučký kůň&hellip;', $this->checkStringCut('žluťoučký kůň příšerně úpěl ďábelské ódy', 14, '&hellip;'));

		// Short
		$shorty = '1234567890';
		$this->assertEquals($shorty, $this->checkStringCut($shorty));
		$this->assertEquals('12...', $this->checkStringCut($shorty, 5));

	}

	/**
	 * Tests word trimming.
	 */
	public function testCutWords()
	{
		$this->assertEquals('žluťoučký kůň příšerně úpěl ďábelské ódy', $this->checkStringWordCut('žluťoučký kůň příšerně úpěl ďábelské ódy', 10));
		$this->assertEquals('žluťo... kůň příšerně úpěl ďábelské ódy', $this->checkStringWordCut('žluťoučký kůň příšerně úpěl ďábelské ódy', 8));
		$this->assertEquals('žl... kůň př... úpěl ďá... ódy', $this->checkStringWordCut('žluťoučký kůň příšerně úpěl ďábelské ódy', 5));

		// Word boundary just at the end
		$this->assertEquals('abcdefghijk... pqrst', $this->checkStringWordCut('abcdefghijklmno pqrst', 14));
		$this->assertEquals('abcdefghijklmn opqrst', $this->checkStringWordCut('abcdefghijklmn opqrst', 14));
		$this->assertEquals('abcdefghijklm nopqrst', $this->checkStringWordCut('abcdefghijklm nopqrst', 14));

		// Short
		$shorty = '12345678';
		$this->assertEquals($shorty, $this->checkStringWordCut($shorty));
		$this->assertEquals('12...', $this->checkStringWordCut($shorty, 5));
	}

	/**
	 * Checks one string.
	 *
	 * @param string $string Input string
	 * @param integer $max Max length
	 * @param string $etc "Etc" definition
	 * @return string
	 */
	private function checkStringWordCut($string, $max = 8, $etc = '...')
	{
		$cut = StringUtil::cutWords($string, $max, $etc);

		// &hellip; has length of 1
		$cut2 = strtr(html_entity_decode($cut, ENT_COMPAT, 'utf-8'), array('&hellip;' => '.'));

		$words = preg_split('~\\s+~', $string);
		$trimmedWords = preg_split('~\\s+~', $cut2);

		$this->assertEquals(count($trimmedWords), count($words));

		foreach ($words as $i => $word) {
			if (mb_strlen($word, 'utf-8') <= $max) {
				$this->assertEquals($word, $trimmedWords[$i], 'Word trimmed even though it was short enough');
			} else {
				$this->assertLessThanOrEqual($max, mb_strlen($trimmedWords[$i], 'utf-8'));
				$this->assertRegExp('~' . preg_quote($etc === '&hellip;' ? '.' : $etc) . '$~', $trimmedWords[$i], 'String does not end with ' . $etc);
			}
		}

		return $cut;
	}

	/**
	 * Checks one string.
	 *
	 * @param string $string Input string
	 * @param integer $max Max length
	 * @param string $etc "Etc" definition
	 * @return string
	 */
	private function checkStringCut($string, $max = 16, $etc = '...')
	{
		$cut = StringUtil::cut($string, $max, $etc);
		// &hellip; has length of 1
		$cutLength = mb_strlen(strtr(html_entity_decode($cut), array('&hellip;' => '.')), 'utf-8');
		$this->assertLessThanOrEqual($max, $cutLength, 'String is longer');

		if (mb_strlen($string, 'utf-8') <= $max) {
			$this->assertEquals($string, $cut, 'String trimmed even though it was short enough');
		} else {
			$this->assertRegExp('~' . preg_quote($etc) . '$~', $cut, 'String does not end with ' . $etc);
		}
		return $cut;
	}

	/**
	 * Test the crc generator.
	 */
	public function testCrc()
	{
		$this->assertSame(-662733300, StringUtil::crc('test'));
		$this->assertSame(-33591962, StringUtil::crc('žluťoučký kůň příšerně úpěl ďábelské ódy'));
	}

	/**
	 * Tests the random string generator.
	 */
	public function testRandom()
	{
		for ($i = 1; $i <= 32; $i++) {
			$random = StringUtil::random($i);
			$this->assertEquals($i, strlen($random));
			$this->assertRegExp('~^[a-z0-9]+$~i', $random);
		}
	}

	/**
	 * Tests line ending conversions.
	 */
	public function testFixLineEnding()
	{
		$tests = array(
			"test\r\nžlutý\r\n",
			"test\ržlutý\r",
			"test\r\nžlutý\r",
			"test\nžlutý\r",
			"test\nžlutý\r\n"
		);

		// No line ending given
		foreach ($tests as $test) {
			$this->assertEquals("test\nžlutý\n", StringUtil::fixLineEnding($test));
			$this->assertNotEquals($test, StringUtil::fixLineEnding($test));
		}
		$this->assertEquals("test\nžlutý\n", StringUtil::fixLineEnding("test\nžlutý\n"));

		// Line ending given
		foreach ($tests as $test) {
			foreach (array("\n", "\r", "\r\n") as $ending) {
				$this->assertEquals(sprintf('test%1$sžlutý%1$s', $ending), StringUtil::fixLineEnding($test, $ending));
				$this->assertNotEquals("test\nžlutý\r\n", StringUtil::fixLineEnding($test, $ending));
			}
		}
	}

	/**
	 * Tests email address obfuscation.
	 */
	public function testObfuscateEmail()
	{
		$email = 'example@example.com';

		$this->assertEquals('example&#64;example.com', StringUtil::obfuscateEmail($email));
		$this->assertEquals('example&#64;<!---->example.com', StringUtil::obfuscateEmail($email, true));
	}

	/**
	 * Tests first letter lowercase.
	 */
	public function testLcfirst()
	{
		$this->assertEquals('žlutý kůň', StringUtil::lcfirst('Žlutý kůň'));
		$this->assertEquals('žlutý kůň', StringUtil::lcfirst('žlutý kůň'));
	}

	/**
	 * Tests special HTML characters escaping.
	 */
	public function testEscape()
	{
		$this->assertEquals('test &amp; test', StringUtil::escape('test & test'));
		$this->assertEquals('&quot;test&quot; &amp; &#039;test&#039;', StringUtil::escape('"test" & \'test\''));
		$this->assertEquals('&quot;test&quot; &amp; \'test\'', StringUtil::escape('"test" & \'test\'', ENT_COMPAT));
		$this->assertEquals('"test" &amp; \'test\'', StringUtil::escape('"test" & \'test\'', ENT_NOQUOTES));
		$this->assertEquals('test &amp; test', StringUtil::escape('test &amp; test'));
		$this->assertEquals('test &amp;amp; test', StringUtil::escape('test &amp; test', ENT_QUOTES, true));
	}

	/**
	 * Tests byte size conversion.
	 */
	public function testFormatBytes()
	{
		$this->assertEquals('11 B', StringUtil::formatBytes(10.5));
		$this->assertEquals('11 B', StringUtil::formatBytes(10.5, '.'));
		$this->assertEquals('11 B', StringUtil::formatBytes(10.5, '.', ','));

		$this->assertEquals('1,0 GB', StringUtil::formatBytes(1073741824));
		$this->assertEquals('1.0 GB', StringUtil::formatBytes(1073741824, '.'));
		$this->assertEquals('10,240.0 PB', StringUtil::formatBytes(11805916207174113034240, '.', ','));
	}
}
