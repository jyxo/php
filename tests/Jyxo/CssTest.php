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

use LogicException;
use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function sprintf;

/**
 * Test for the \Jyxo\Css class.
 *
 * @see \Jyxo\Css
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class CssTest extends TestCase
{

	/**
	 * Path to the files.
	 *
	 * @var string
	 */
	private $filePath;

	/**
	 * Tests the constructor.
	 *
	 * @see \Jyxo\Css::__construct()
	 */
	public function testConstruct(): void
	{
		$this->expectException(LogicException::class);
		$css = new Css();
	}

	/**
	 * Test the repair() method.
	 *
	 * @see \Jyxo\Css::repair()
	 */
	public function testRepair(): void
	{
		// In the form: expected css, given css
		$tests = [];

		// Converts property names to lowercase
		$tests[] = ['html { margin : 10px 20px 10px; color: black}', 'html { MARGIN : 10px 20px 10px; COLOR: black}'];
		$tests[] = ['margin: 10px 20px 10px; color: black;', 'MARGIN: 10px 20px 10px; COLOR: black;'];

		// Converts rgb() and url() to lowercase
		$tests[] = ['background: url (\'background.png\') #ffffff;', 'background: URL (\'background.png\') RGB (255, 255, 255);'];

		// Remove properties without definitions
		$tests[] = ['border: solid 1px black;', 'border: solid 1px black; color:; color:; color:'];
		$tests[] = ['border: solid 1px black;', 'border: solid 1px black; color: ;'];
		$tests[] = ['border: solid 1px black;', 'border: solid 1px black; color:'];
		$tests[] = ['{border: solid 1px black;}', '{border: solid 1px black; color: }'];
		$tests[] = ['{border: solid 1px black; } ', '{border: solid 1px black; color : ; } '];

		// Remove MS Word properties
		$tests[] = ['{}', '{mso-bidi-font-weight: normal; mso-bidi-font-weight: normal; mso-ascii-theme-font: minor-latin; mso-fareast-font-family: Calibri; mso-ansi-language: CS; mso-hansi-theme-font: minor-latin;}'];
		$tests[] = ['{color: black;}', '{color: black; mso-bidi-font-weight: normal}'];
		$tests[] = ['{color: black;}', '{color: black; mso-bidi-font-weight : }'];
		$tests[] = ['color: black;', 'color: black; mso-bidi-font-weight:'];

		// Converts colors to lowercase
		$tests[] = ['color: #aabbcc;', 'color: #aaBBcc;'];
		$tests[] = ['color: #aabbcc', 'color: #aabbcc'];
		$tests[] = ['color:#aa00cc;', 'color:#Aa00Cc;'];

		// Converts color from RGB to HEX
		$tests[] = ['color: #ffffff;', 'color: rgb(255, 255, 255);'];
		$tests[] = ['color:#000000', 'color:rgb (0,0,0)'];
		$tests[] = ['color: #a4a2a3;', 'color: RGB( 164 , 162 , 163 );'];

		foreach ($tests as $no => $test) {
			$this->assertEquals($test[0], Css::repair($test[1]), sprintf('Test %s', $no + 1));
		}
	}

	/**
	 * Tests the filterProperties() method.
	 *
	 * @see \Jyxo\Css::filterProperties()
	 */
	public function testFilterProperties(): void
	{
		// Filters given properties
		$this->assertEquals(
			'{border: solid 1px black; padding: 10px;}',
			Css::filterProperties('{border: solid 1px black; color: black; padding: 10px;}', ['color'])
		);
		$this->assertEquals(
			'border:solid 1px black;padding:10px',
			Css::filterProperties('border:solid 1px black;color:black;padding:10px', ['color'])
		);
		$this->assertEquals(
			'border:solid 1px black;',
			Css::filterProperties('border:solid 1px black;padding:10px', ['padding'])
		);
		$this->assertEquals(
			'{border:solid 1px black}',
			Css::filterProperties('{padding:10px;border:solid 1px black}', ['padding'])
		);
		$this->assertEquals(
			'{}',
			Css::filterProperties('{color: #000000; padding: 10px; border: solid 1px black;}', ['color', 'border', 'padding'])
		);

		// Keeps given properties and keeps everything else
		$this->assertEquals(
			'{ color: black;}',
			Css::filterProperties('{border: solid 1px black; color: black; padding: 10px;}', ['color'], false)
		);
		$this->assertEquals(
			'color:black;',
			Css::filterProperties('border:solid 1px black;color:black;padding:10px', ['color'], false)
		);
		$this->assertEquals(
			'padding:10px',
			Css::filterProperties('border:solid 1px black;padding:10px', ['padding'], false)
		);
		$this->assertEquals(
			'{padding:10px;}',
			Css::filterProperties('{padding:10px;border:solid 1px black}', ['padding'], false)
		);
		$this->assertEquals(
			'{color: #000000; padding: 10px; border: solid 1px black;}',
			Css::filterProperties('{color: #000000; padding: 10px; border: solid 1px black;}', ['color', 'border', 'padding'], false)
		);
	}

	/**
	 * Tests the minify() method.
	 *
	 * @see \Jyxo\Css::minify()
	 */
	public function testMinify(): void
	{
		$this->assertStringEqualsFile(
			$this->filePath . '/minify-expected.css',
			Css::minify(file_get_contents($this->filePath . '/minify.css'))
		);

		// Color conversion
		$this->assertEquals(
			'background:url(\'image.png\') #000 top left;color:#999;border:solid 1px #cfc',
			Css::minify('background: url( \'image.png\' ) #000000 top left; color: #999999; border: solid 1px #ccffcc;')
		);
	}

	/**
	 * Tests the convertStyleToInline() method.
	 *
	 * @see \Jyxo\Css::convertStyleToInline()
	 */
	public function testConvertStyleToInline(): void
	{
		$testCount = 10;

		for ($i = 1; $i <= $testCount; $i++) {
			$this->assertStringEqualsFile(
				$this->filePath . '/' . sprintf('convertstyle-%s-expected.html', $i),
				Css::convertStyleToInline(file_get_contents($this->filePath . '/' . sprintf('convertstyle-%s.html', $i))),
				sprintf('Failed test %s for method %s::convertStyleToInline().', $i, Css::class)
			);
		}
	}

	/**
	 * Prepares the testing environment.
	 */
	protected function setUp(): void
	{
		$this->filePath = DIR_FILES . '/css';
	}

}
