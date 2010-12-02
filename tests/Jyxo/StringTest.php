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
 * Test práce s řetězci.
 *
 * @author Jakub Tománek <libs@jyxo.com>
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Test ořezání.
	 */
	public function testCut()
	{
		// Zkrácení na mezeře
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		// Zkrácení na tečce
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň.Příšerně úpěl ďábelské ódy'));
		// Zkrácení na tečce s mezerou
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň. Příšerně úpěl ďábelské ódy'));
		// Zkrácení na čárce
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň,příšerně úpěl ďábelské ódy'));
		// Zkrácení na středníku
		$this->assertEquals('žluťoučký kůň...', $this->checkString('žluťoučký kůň;příšerně úpěl ďábelské ódy'));

		// Hranice slova těsně na konci
		$this->assertEquals('abcdefghijklm...', $this->checkString('abcdefghijklmno pqrst'));
		$this->assertEquals('abcdefghijklm...', $this->checkString('abcdefghijklmn opqrst'));
		$this->assertEquals('abcdefghijklm...', $this->checkString('abcdefghijklm nopqrst'));

		// Bez hranic slov
		$this->assertEquals('abcdefghijklm...', $this->checkString('abcdefghijklmnopqrstuvwxyz'));

		// Tři tečky jako HTML entita
		$this->assertEquals('žluťoučký kůň&hellip;', $this->checkString('žluťoučký kůň příšerně úpěl ďábelské ódy', 14, '&hellip;'));

		// Krátké
		$shorty = '1234567890';
		$this->assertEquals($shorty, $this->checkString($shorty));
		$this->assertEquals('12...', $this->checkString($shorty, 5));

	} // testCut();

	/**
	 * Kontroluje jeden řetězec.
	 *
	 * @param string $string
	 * @param int $max
	 * @param string $etc
	 * @return string
	 */
	private function checkString($string, $max = 16, $etc = '...')
	{
		$cut = \Jyxo\String::cut($string, $max, $etc);
		// &hellip; má délku 1
		$cutLength = mb_strlen(strtr(html_entity_decode($cut), array('&hellip;' => '.')));
		$this->assertLessThanOrEqual($max, $cutLength, 'String is longer');

		if (mb_strlen($string) <= $max) {
			$this->assertEquals($string, $cut, 'String cutted even though it was shorter than enough');
		} else {
			$this->assertRegExp('~' . preg_quote($etc) . '$~', $cut, 'String does not end with ' . $etc);
		}
		return $cut;
	} // checkString();

	/**
	 * Test konverzních metod.
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
	} // testConvert();

	/**
	 * Test crc.
	 */
	public function testCrc()
	{
		$this->assertSame(-662733300, \Jyxo\String::crc('test'));
		$this->assertSame(-33591962, \Jyxo\String::crc('žluťoučký kůň příšerně úpěl ďábelské ódy'));
	}

	/**
	 * Test generátoru náhodných řetězců.
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
	 * Test kontroly UTF-8.
	 */
	public function testCheckUtf()
	{
		$this->assertTrue(\Jyxo\String::checkUtf('žluťoučký kůň pěl ďábelské ódy'));
		$this->assertTrue(\Jyxo\String::checkUtf('Государственный гимн Российской Федерации'));
		$this->assertFalse(\Jyxo\String::checkUtf(file_get_contents(DIR_FILES . '/string/cp1250.txt')));
		$this->assertFalse(\Jyxo\String::checkUtf(file_get_contents(DIR_FILES . '/string/iso-8859-2.txt')));
	}

	/**
	 * Test opravy UTF-8.
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
	 * Test převodu konců řádků.
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

		// Nezadaný konec řádků
		foreach ($tests as $test) {
			$this->assertEquals("test\nžlutý\n", \Jyxo\String::fixLineEnding($test));
			$this->assertNotEquals($test, \Jyxo\String::fixLineEnding($test));
		}
		$this->assertEquals("test\nžlutý\n", \Jyxo\String::fixLineEnding("test\nžlutý\n"));

		// Zadaný konec řádků
		foreach ($tests as $test) {
			foreach (array("\n", "\r", "\r\n") as $ending) {
				$this->assertEquals(sprintf('test%1$sžlutý%1$s', $ending), \Jyxo\String::fixLineEnding($test, $ending));
				$this->assertNotEquals("test\nžlutý\r\n", \Jyxo\String::fixLineEnding($test, $ending));
			}
		}
	}

	/**
	 * Test obfuscování e-mailu
	 */
	public function testObfuscateEmail()
	{
		$email = 'example@example.com';

		$this->assertEquals('example&#64;example.com', \Jyxo\String::obfuscateEmail($email));
		$this->assertEquals('example&#64;<!---->example.com', \Jyxo\String::obfuscateEmail($email, true));
	}

	/**
	 * Test převodu prvního písmene na malé písmeno.
	 */
	public function testLcfirst()
	{
		$this->assertEquals('žlutý kůň', \Jyxo\String::lcfirst('Žlutý kůň'));
		$this->assertEquals('žlutý kůň', \Jyxo\String::lcfirst('žlutý kůň'));
	}

	/**
	 * Test k escapování speciálních HTML znaků.
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
	 * Test převodu velikosti zadané v bytech na kB, MB, GB, TB či PB.
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
