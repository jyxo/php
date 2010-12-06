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

namespace Jyxo;

require_once __DIR__ . '/../bootstrap.php';

/**
 * String processing test.
 *
 * @author Jakub Tománek <libs@jyxo.com>
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests string trimming.
	 */
	public function testCut()
	{
		// Trim on space
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		// Trim on period
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň.Příšerně úpěl ďábelské ódy'));
		// Trim on period and space
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň. Příšerně úpěl ďábelské ódy'));
		// Trim on comma
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň,příšerně úpěl ďábelské ódy'));
		// Trim on semicolon
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň;příšerně úpěl ďábelské ódy'));

		// Word boundary just at the end
		$this->assertEquals('abcdefghijklm...', $this->checkString('abcdefghijklmno pqrst'));
		$this->assertEquals('abcdefghijklm...', $this->checkString('abcdefghijklmn opqrst'));
		$this->assertEquals('abcdefghijklm...', $this->checkString('abcdefghijklm nopqrst'));

		// No word boundaries
		$this->assertEquals('abcdefghijklm...', $this->checkString('abcdefghijklmnopqrstuvwxyz'));

		// Etc as HTML entity
		$this->assertEquals('žluťoučký kůň&hellip;', $this->checkString('žluťoučký kůň příšerně úpěl ďábelské ódy', 14, '&hellip;'));

		// Short
		$shorty = '1234567890';
		$this->assertEquals($shorty, $this->checkString($shorty));
		$this->assertEquals('12...', $this->checkString($shorty, 5));

	}

	/**
	 * Checks one string.
	 *
	 * @param string $string Input string
	 * @param integer $max Max length
	 * @param string $etc "Etc" definition
	 * @return string
	 */
	private function checkString($string, $max = 16, $etc = '...')
	{
		$cut = \Jyxo\String::cut($string, $max, $etc);
		// &hellip; has length of 1
		$cutLength = mb_strlen(strtr(html_entity_decode($cut), array('&hellip;' => '.')));
		$this->assertLessThanOrEqual($max, $cutLength, 'String is longer');

		if (mb_strlen($string) <= $max) {
			$this->assertEquals($string, $cut, 'String cutted even though it was shorter than enough');
		} else {
			$this->assertRegExp('~' . preg_quote($etc) . '$~', $cut, 'String does not end with ' . $etc);
		}
		return $cut;
	}

	/**
	 * Tests conversion functions.
	 */
	public function testConvert()
	{
		$this->assertEquals('abc', \Jyxo\String::utf2iso('abc'));
		$this->assertNotEquals('žluťoučký kůň příšerně úpěl ďábelské ódy', \Jyxo\String::utf2iso('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		$this->assertRegExp('~^[a-z0-9]([a-z0-9\-]*[a-z0-9])?$~', \Jyxo\String::utf2ident('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		$this->assertEquals('zlutoucky kun priserne upel dabelske ody', \Jyxo\String::utf2ascii('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		$this->assertEquals('zlutoucky kun priserne upel dabelske ody', \Jyxo\String::win2ascii(file_get_contents(DIR_FILES . '/string/cp1250.txt')));
		$this->assertEquals('zlutoucky kun priserne upel dabelske ody', \Jyxo\String::iso2ascii(file_get_contents(DIR_FILES . '/string/iso-8859-2.txt')));
		$this->assertEquals(file_get_contents(DIR_FILES . '/string/iso-8859-2.txt'), \Jyxo\String::utf2iso('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		$this->assertEquals('zlutoucky-kun-priserne-upel-dabelske-ody', \Jyxo\String::utf2ident('?žluťoučký  +  kůň příšerně úpěl ďábelské ódy...'));
		$this->assertEquals('Rossija', \Jyxo\String::russian2ascii('Россия'));
		$this->assertEquals('Gosudarstvennyj gimn Rossijskoj Federacii', \Jyxo\String::russian2ascii('Государственный гимн Российской Федерации'));
	}

	/**
	 * Test the crc generator.
	 */
	public function testCrc()
	{
		$this->assertSame(-662733300, \Jyxo\String::crc('test'));
		$this->assertSame(-33591962, \Jyxo\String::crc('žluťoučký kůň příšerně úpěl ďábelské ódy'));
	}

	/**
	 * Tests the random string generator.
	 */
	public function testRandom()
	{
		for ($i = 1; $i <= 32; $i++) {
			$random = \Jyxo\String::random($i);
			$this->assertEquals($i, strlen($random));
			$this->assertRegExp('~^[a-z0-9]+$~i', $random);
		}
	}

	/**
	 * Tests UTF-8 checking.
	 */
	public function testCheckUtf()
	{
		$this->assertTrue(\Jyxo\String::checkUtf('žluťoučký kůň pěl ďábelské ódy'));
		$this->assertTrue(\Jyxo\String::checkUtf('Государственный гимн Российской Федерации'));
		$this->assertFalse(\Jyxo\String::checkUtf(file_get_contents(DIR_FILES . '/string/cp1250.txt')));
		$this->assertFalse(\Jyxo\String::checkUtf(file_get_contents(DIR_FILES . '/string/iso-8859-2.txt')));
	}

	/**
	 * Tests UTF-8 fixing.
	 */
	public function testFixUtf()
	{
		$this->assertEquals('žluťoučký kůň pěl ďábelské ódy', \Jyxo\String::fixUtf('žluťoučký kůň pěl ďábelské ódy'));
		$this->assertEquals('Государственный гимн Российской Федерации', \Jyxo\String::fixUtf('Государственный гимн Российской Федерации'));

		$expected = 'glibc' === ICONV_IMPL ? '' : 'luouk k pern pl belsk ';
		$this->assertEquals($expected, \Jyxo\String::fixUtf(file_get_contents(DIR_FILES . '/string/cp1250.txt')));
		$this->assertEquals($expected, \Jyxo\String::fixUtf(file_get_contents(DIR_FILES . '/string/iso-8859-2.txt')));
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
			$this->assertEquals("test\nžlutý\n", \Jyxo\String::fixLineEnding($test));
			$this->assertNotEquals($test, \Jyxo\String::fixLineEnding($test));
		}
		$this->assertEquals("test\nžlutý\n", \Jyxo\String::fixLineEnding("test\nžlutý\n"));

		// Line ending given
		foreach ($tests as $test) {
			foreach (array("\n", "\r", "\r\n") as $ending) {
				$this->assertEquals(sprintf('test%1$sžlutý%1$s', $ending), \Jyxo\String::fixLineEnding($test, $ending));
				$this->assertNotEquals("test\nžlutý\r\n", \Jyxo\String::fixLineEnding($test, $ending));
			}
		}
	}

	/**
	 * Tests email address obfuscation.
	 */
	public function testObfuscateEmail()
	{
		$email = 'example@example.com';

		$this->assertEquals('example&#64;example.com', \Jyxo\String::obfuscateEmail($email));
		$this->assertEquals('example&#64;<!---->example.com', \Jyxo\String::obfuscateEmail($email, true));
	}

	/**
	 * Tests first letter lowercase.
	 */
	public function testLcfirst()
	{
		$this->assertEquals('žlutý kůň', \Jyxo\String::lcfirst('Žlutý kůň'));
		$this->assertEquals('žlutý kůň', \Jyxo\String::lcfirst('žlutý kůň'));
	}

	/**
	 * Tests special HTML characters escaping.
	 */
	public function testEscape()
	{
		$this->assertEquals('test &amp; test', \Jyxo\String::escape('test & test'));
		$this->assertEquals('&quot;test&quot; &amp; &#039;test&#039;', \Jyxo\String::escape('"test" & \'test\''));
		$this->assertEquals('&quot;test&quot; &amp; \'test\'', \Jyxo\String::escape('"test" & \'test\'', ENT_COMPAT));
		$this->assertEquals('"test" &amp; \'test\'', \Jyxo\String::escape('"test" & \'test\'', ENT_NOQUOTES));
		$this->assertEquals('test &amp; test', \Jyxo\String::escape('test &amp; test'));
		$this->assertEquals('test &amp;amp; test', \Jyxo\String::escape('test &amp; test', ENT_QUOTES, true));
	}

	/**
	 * Tests byte size conversion.
	 */
	public function testFormatBytes()
	{
		$this->assertEquals('11 B', \Jyxo\String::formatBytes(10.5));
		$this->assertEquals('11 B', \Jyxo\String::formatBytes(10.5, '.'));
		$this->assertEquals('11 B', \Jyxo\String::formatBytes(10.5, '.', ','));

		$this->assertEquals('1,0 GB', \Jyxo\String::formatBytes(1073741824));
		$this->assertEquals('1.0 GB', \Jyxo\String::formatBytes(1073741824, '.'));
		$this->assertEquals('10,240.0 PB', \Jyxo\String::formatBytes(11805916207174113034240, '.', ','));
	}
}
