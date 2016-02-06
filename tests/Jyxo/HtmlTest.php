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
 * Class \Jyxo\Html test.
 *
 * @see \Jyxo\Html
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class HtmlTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Path to testing files.
	 *
	 * @var string
	 */
	private $filePath;

	/**
	 * Prepares the testing environment.
	 */
	protected function setUp()
	{
		$this->filePath = DIR_FILES . '/html';
	}

	/**
	 * Tests the __construct() method.
	 *
	 * @see \Jyxo\Html::__construct()
	 */
	public function testConstruct()
	{
		$this->expectException(\LogicException::class);
		$html = new Html();
	}

	/**
	 * Tests the is() method.
	 *
	 * @see \Jyxo\Html::is()
	 */
	public function testIs()
	{
		$this->assertTrue(Html::is('foo <b>bar</b>'));
		$this->assertTrue(Html::is('<a href="http://jyxo.cz">boo</a>'));
		$this->assertFalse(Html::is('foo bar'));
		$this->assertFalse(Html::is('one < two'));
		$this->assertFalse(Html::is('foo <br<>'));
		$this->assertFalse(Html::is('<http://blog.cz/>'));
	}

	/**
	 * Tests the repair() method.
	 *
	 * @see \Jyxo\Html::repair()
	 */
	public function testRepair()
	{
		$this->assertStringEqualsFile(
			$this->filePath . '/repair-expected.html',
			Html::repair(file_get_contents($this->filePath . '/repair.html'))
		);
	}

	/**
	 * Tests the removeTags() method.
	 *
	 * @see \Jyxo\Html::removeTags()
	 */
	public function testRemoveTags()
	{
		$this->assertStringEqualsFile(
			$this->filePath . '/removetags-1-expected.html',
			Html::removeTags(file_get_contents($this->filePath . '/removetags-1.html'))
		);

		$this->assertStringEqualsFile(
			$this->filePath . '/removetags-2-expected.html',
			Html::removeTags(file_get_contents($this->filePath . '/removetags-2.html'), array('p', 'select', 'ul'))
		);
	}

	/**
	 * Tests the removeInnerTags() method.
	 *
	 * @see \Jyxo\Html::removeInnerTags()
	 */
	public function testRemoveInnerTags()
	{
		$this->assertEquals(
			"<i>slovo1</i>\nslovo2\n<i>slovo3slovo4slovo5</i>",
			Html::removeInnerTags("<i>slovo1</i>\nslovo2\n<i>slovo3<i>slovo4</i>slovo5</i>", 'i')
		);
		$this->assertEquals(
			"<strong>slovo1</strong>\nslovo2\n<strong>slovo3slovo4slovoslovo5</strong>",
			Html::removeInnerTags("<strong>slovo1</strong>\nslovo2\n<strong>slovo3<strong>slovo4</strong>slovo<strong>slovo5</strong></strong>", 'strong')
		);
		$this->assertEquals(
			"<strong>slovo1</strong>\nslovo2\n<strong>slovo3<b>slovo4</b>slovo5</strong>",
			Html::removeInnerTags("<strong>slovo1</strong>\nslovo2\n<strong>slovo3<b>slovo4</b>slovo5</strong>", 'strong')
		);
		$this->assertEquals(
			"<b>slovo1</b>\nslovo2\n<b>slovo3 slovo4 slovo5</b>",
			Html::removeInnerTags("<b>slovo1</b>\nslovo2\n<b>slovo3 slovo4 slovo5</b>", 'strong')
		);
	}

	/**
	 * Tests the removeAttributes() method.
	 *
	 * @see \Jyxo\Html::removeAttributes()
	 */
	public function testRemoveAttributes()
	{
		$this->assertStringEqualsFile(
			$this->filePath . '/removeattributes-1-expected.html',
			Html::removeAttributes(file_get_contents($this->filePath . '/removeattributes-1.html'))
		);

		$this->assertStringEqualsFile(
			$this->filePath . '/removeattributes-2-expected.html',
			Html::removeAttributes(file_get_contents($this->filePath . '/removeattributes-2.html'), array('href', 'title'))
		);
	}

	/**
	 * Tests the removeJavascriptEvents() method.
	 *
	 * @see \Jyxo\Html::removeJavascriptEvents()
	 */
	public function testRemoveJavascriptEvents()
	{
		$this->assertStringEqualsFile(
			$this->filePath . '/removejavascriptevents-expected.html',
			Html::removeJavascriptEvents(file_get_contents($this->filePath . '/removejavascriptevents.html'))
		);
	}

	/**
	 * Tests the removeRemoteImages() method.
	 *
	 * @see \Jyxo\Html::removeRemoteImages()
	 */
	public function testRemoveRemoteImages()
	{
		// In format (expected value, input value)
		$tests = array(
			array(
				'<img  width="10"    SRC="about:blank"    />',
				'<img  width="10"    SRC="http://domain.tld/picture.png"    />'
			),
			array(
				'<body  bgcolor="green"    BACKGROUND=""    >',
				'<body  bgcolor="green"    BACKGROUND="http://domain.tld/picture.png"    >'
			),
			array(
				'<a  href="#"    style="font: sans-serif;   background  : center center ; color: green;"    >',
				'<a  href="#"    style="font: sans-serif;   background  : center center url(\'https://domain.tld/picture.png\'); color: green;"    >'
			),
			array(
				'<a  href="#"    style="font: sans-serif;    color: green;"    >',
				'<a  href="#"    style="font: sans-serif;   background-image  : url(\'http://domain.tld/picture.png\'); color: green;"    >'
			),
			array(
				'<li  href="#"    style="font: sans-serif;   list-style  : circle ; color: green;"    >',
				'<li  href="#"    style="font: sans-serif;   list-style  : circle url(\'http://domain.tld/picture.png\'); color: green;"    >'
			),
			array(
				'<li  href="#"    style="font: sans-serif;    color: green;"    >',
				'<li  href="#"    style="font: sans-serif;   list-style-image  : url(\'http://domain.tld/picture.png\'); color: green;"    >'
			),
			array(
				'<img src="data:" />',
				'<img src="data:" />'
			)
		);

		foreach ($tests as $test) {
			$this->assertEquals(
				$test[0],
				Html::removeRemoteImages($test[1])
			);
		}
	}

	/**
	 * Tests the removeDangerous() method.
	 *
	 * @see \Jyxo\Html::removeDangerous()
	 */
	public function testRemoveDangerous()
	{
		$this->assertStringEqualsFile(
			$this->filePath . '/removedangerous-expected.html',
			Html::removeDangerous(file_get_contents($this->filePath . '/removedangerous.html'))
		);
	}

	/**
	 * Tests the getBody() method.
	 *
	 * @see \Jyxo\Html::getBody()
	 */
	public function testGetBody()
	{
		$testCount = 2;

		for ($i = 1; $i <= $testCount; $i++) {
			$this->assertStringEqualsFile(
				$this->filePath . '/' . sprintf('getbody-%s-expected.html', $i),
				Html::getBody(file_get_contents($this->filePath . '/' . sprintf('getbody-%s.html', $i))),
				sprintf('Failed test %s for method \Jyxo\Html::getBody.', $i)
			);
		}
	}

	/**
	 * Tests the fromText() method.
	 *
	 * @see \Jyxo\Html::fromText()
	 */
	public function testFromText()
	{
		$testCount = 2;

		for ($i = 1; $i <= $testCount; $i++) {
			$this->assertStringEqualsFile(
				$this->filePath . '/' . sprintf('fromtext-%s-expected.html', $i),
				Html::fromText(file_get_contents($this->filePath . '/' . sprintf('fromtext-%s.txt', $i))),
				sprintf('Failed test %s for method \Jyxo\Html::fromText.', $i)
			);
		}
	}

	/**
	 * Tests the linkFromText() method.
	 *
	 * @see \Jyxo\Html::linkFromText()
	 */
	public function testLinkFromText()
	{
		$this->assertStringEqualsFile(
			$this->filePath . '/linkfromtext-expected.html',
			Html::linkFromText(file_get_contents($this->filePath . '/linkfromtext.txt'))
		);
	}

	/**
	 * Tests the toText() method.
	 *
	 * @see \Jyxo\Html::toText()
	 */
	public function testToText()
	{
		$testCount = 6;

		for ($i = 1; $i <= $testCount; $i++) {
			$this->assertStringEqualsFile(
				$this->filePath . '/' . sprintf('totext-%s-expected.txt', $i),
				Html::toText(file_get_contents($this->filePath . '/' . sprintf('totext-%s.html', $i))),
				sprintf('Failed test %s for method \Jyxo\Html::toText.', $i)
			);
		}
	}
}
