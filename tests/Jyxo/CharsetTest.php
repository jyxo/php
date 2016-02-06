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

/**
 * Charset processing test.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 * @author Ondřej Nešpor
 */
class CharsetTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Tests detect function.
	 */
	public function testDetect()
	{
		$this->assertEquals('UTF-8', Charset::detect('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		$this->assertEquals('UTF-8', Charset::detect('Государственный гимн Российской Федерации'));

		$this->assertEquals('WINDOWS-1250', Charset::detect(file_get_contents(DIR_FILES . '/charset/cp1250.txt')));
		$this->assertEquals('ISO-8859-2', Charset::detect(file_get_contents(DIR_FILES . '/charset/iso-8859-2.txt')));
	}

	/**
	 * Tests conversion functions.
	 */
	public function testConvert()
	{
		$this->assertRegExp('~^[a-z0-9]([a-z0-9\-]*[a-z0-9])?$~', Charset::utf2ident('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		$this->assertEquals('zlutoucky-kun-priserne-upel-dabelske-ody', Charset::utf2ident('?žluťoučký  +  kůň příšerně úpěl ďábelské ódy...'));
		$this->assertEquals('zlutoucky kun priserne upel dabelske ody', Charset::utf2ascii('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		$this->assertEquals('zlutoucky kun priserne upel dabelske ody', Charset::win2ascii(file_get_contents(DIR_FILES . '/charset/cp1250.txt')));
		$this->assertEquals('zlutoucky kun priserne upel dabelske ody', Charset::iso2ascii(file_get_contents(DIR_FILES . '/charset/iso-8859-2.txt')));
		$this->assertEquals('Rossija', Charset::russian2ascii('Россия'));
		$this->assertEquals('Gosudarstvennyj gimn Rossijskoj Federacii', Charset::russian2ascii('Государственный гимн Российской Федерации'));

		$this->assertEquals('žluťoučký kůň příšerně úpěl ďábelské ódy', Charset::convert2utf('žluťoučký kůň příšerně úpěl ďábelské ódy'));
		$this->assertEquals('Государственный гимн Российской Федерации', Charset::convert2utf('Государственный гимн Российской Федерации'));
		$this->assertEquals('žluťoučký kůň příšerně úpěl ďábelské ódy', Charset::convert2utf(file_get_contents(DIR_FILES . '/charset/cp1250.txt'), 'windows-1250'));
		$this->assertEquals('žluťoučký kůň příšerně úpěl ďábelské ódy', Charset::convert2utf(file_get_contents(DIR_FILES . '/charset/iso-8859-2.txt')));
	}

	/**
	 * Tests UTF-8 fixing.
	 */
	public function testFixUtf()
	{
		$this->assertEquals('žluťoučký kůň pěl ďábelské ódy', Charset::fixUtf('žluťoučký kůň pěl ďábelské ódy'));
		$this->assertEquals('Государственный гимн Российской Федерации', Charset::fixUtf('Государственный гимн Российской Федерации'));

		$this->assertEquals('', Charset::fixUtf(file_get_contents(DIR_FILES . '/charset/cp1250.txt')));
		$this->assertEquals('', Charset::fixUtf(file_get_contents(DIR_FILES . '/charset/iso-8859-2.txt')));
	}
}
