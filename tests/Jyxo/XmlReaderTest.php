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

namespace Jyxo;

use PHPUnit\Framework\TestCase;

/**
 * Test for the \Jyxo\XmlReader class.
 *
 * @see \Jyxo\XmlReader
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class XmlReaderTest extends TestCase
{

	/**
	 * Xml processor.
	 *
	 * @var XmlReader
	 */
	private $reader;

	/**
	 * File path.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Tests the getTextValue() method.
	 *
	 * @see \Jyxo\XmlReader::getTextValue()
	 */
	public function testGetTextValue(): void
	{
		// In the form: tag (key), expected value (value)
		$tests = [];
		$tests['zero'] = '';
		$tests['one'] = 'word';
		$tests['second'] = 'two words';
		$tests['third'] = 'three simple words';
		$tests['forth'] = "\n\t\tfour words on several lines\n\t";
		$tests['fifth'] = 'fifth test with tags';
		$tests['sixth'] = 'sixth test with tags and inner tags';

		$this->reader->open($this->path . '/text.xml');
		$this->reader->next('test');

		while ($this->reader->read()) {
			if ($this->reader->nodeType === \XMLReader::ELEMENT) {
				$this->assertEquals(
					$tests[$this->reader->name],
					$this->reader->getTextValue()
				);
			}
		}
	}

	/**
	 * Tests the getContent() method.
	 *
	 * @see \Jyxo\XmlReader::getContent()
	 */
	public function testGetContent(): void
	{
		// In the form: tag (key), expected value (value)
		$tests = [];
		$tests['zero'] = '';
		$tests['one'] = 'word';
		$tests['second'] = 'two <tag>words</tag>';
		$tests['third'] = 'three<empty/><tag>simple</tag> words';
		$tests['forth'] = "\n\t\tfour<tag>words</tag>on<empty/>several<empty/>lines\n\t";
		$tests['fifth'] = 'fifth test without tags';
		$tests['sixth'] = 'cdata in sixth test';
		$tests['seventh'] = 'cdata with <empty/><tag>tags</tag> in seventh test';
		$tests['eighth'] = 'eigth test with <tag attribute="value">tags and attributes</tag>';
		$tests['ninth'] = 'ninth test with <tag attribute="value">tags and attributes and <inner>inner tags</inner></tag>';

		$this->reader->open($this->path . '/content.xml');
		$this->reader->next('test');

		while ($this->reader->read()) {
			if ($this->reader->nodeType === \XMLReader::ELEMENT) {
				$this->assertEquals(
					$tests[$this->reader->name],
					$this->reader->getContent()
				);
			}
		}
	}

	/**
	 * Prepares the testing environment.
	 */
	protected function setUp(): void
	{
		$this->reader = new XmlReader();
		$this->path = DIR_FILES . '/xmlreader';
	}

	/**
	 * Cleans up the testing environment.
	 */
	protected function tearDown(): void
	{
		$this->reader->close();
	}

}
