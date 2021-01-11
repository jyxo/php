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

use InvalidArgumentException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Throwable;
use function ob_get_clean;
use function ob_start;
use function sprintf;

/**
 * Class \Jyxo\Color test.
 *
 * @see \Jyxo\Color
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class ColorTest extends TestCase
{

	/**
	 * The whole test.
	 */
	public function test(): void
	{
		$tests = ['#000000', '#000', '000000', 0, [0, 0, 0], new Color('#000000')];

		foreach ($tests as $test) {
			$color = new Color($test);
			$this->assertEquals(0, $color->getRed());
			$this->assertEquals(0, $color->getGreen());
			$this->assertEquals(0, $color->getBlue());
			$this->assertEquals('000000', $color->getHex());
			$this->assertEquals([0, 0, 0], $color->getRgb());
			$this->assertEquals(0, $color->getBinary());

			ob_start();
			echo $color;
			$output = ob_get_clean();
			$this->assertEquals('#000000', $output);

			$this->assertEquals('000000', $color->toGrayScale()->getHex());
			$this->assertEquals('ffffff', $color->toInverse()->getHex());
		}

		$color = new Color();
		$color->setRed('00')
			->setGreen('00')
			->setBlue('00');
		$this->assertEquals('000000', $color->getHex());

		$tests = ['#ffffff', '#FfFFffF', '#fff', 'ffffff', 0xFFFFFF, [255, 255, 255], [260, 320, 1024], new Color('#ffffff')];

		foreach ($tests as $test) {
			$color = new Color($test);
			$this->assertEquals(255, $color->getRed());
			$this->assertEquals(255, $color->getGreen());
			$this->assertEquals(255, $color->getBlue());

			$this->assertEquals('ffffff', $color->getHex());
			$this->assertEquals([255, 255, 255], $color->getRgb());
			$this->assertEquals(1, $color->getBinary());

			ob_start();
			echo $color;
			$output = ob_get_clean();
			$this->assertEquals('#ffffff', $output);

			$this->assertEquals('ffffff', $color->toGrayScale()->getHex());
			$this->assertEquals('000000', $color->toInverse()->getHex());
		}

		$color = new Color();
		$color->setRed('FF')
			->setGreen('FF')
			->setBlue('FF');
		$this->assertEquals('ffffff', $color->getHex());

		$tests = ['#239416', '239416', 0x239416, [35, 148, 22], new Color('#239416')];

		foreach ($tests as $test) {
			$color = new Color($test);
			$this->assertEquals(35, $color->getRed());
			$this->assertEquals(148, $color->getGreen());
			$this->assertEquals(22, $color->getBlue());

			$this->assertEquals('239416', $color->getHex());
			$this->assertEquals([35, 148, 22], $color->getRgb());
			$this->assertEquals(0, $color->getBinary());

			ob_start();
			echo $color;
			$output = ob_get_clean();
			$this->assertEquals('#239416', $output);

			$this->assertEquals('616161', $color->toGrayScale()->getHex());
			$this->assertEquals('dc6be9', $color->toInverse()->getHex());
		}

		$tests = ['#22FF66', '#22Ff66', '#2f6', '2F6', '22FF66', '22fF66', 0x22FF66, [34, 255, 102], new Color('#22FF66')];

		foreach ($tests as $test) {
			$color = new Color($test);
			$this->assertEquals(34, $color->getRed());
			$this->assertEquals(255, $color->getGreen());
			$this->assertEquals(102, $color->getBlue());

			$this->assertEquals('22ff66', $color->getHex());
			$this->assertEquals([34, 255, 102], $color->getRgb());
			$this->assertEquals(1, $color->getBinary());

			ob_start();
			echo $color;
			$output = ob_get_clean();
			$this->assertEquals('#22ff66', $output);

			$this->assertEquals('b8b8b8', $color->toGrayScale()->getHex());
			$this->assertEquals('dd0099', $color->toInverse()->getHex());
		}

		$color = new Color();
		$color->setRed('23')
			->setGreen('94')
			->setBlue('16');
		$this->assertEquals('239416', $color->getHex());

		$invalids = ['#FFBBC', '#FBCA', '0000', '0', 'AB'];

		foreach ($invalids as $invalid) {
			try {
				$color = new Color($invalid);
				$this->fail(sprintf('%s expected for value %s', InvalidArgumentException::class, $invalid));
			} catch (AssertionFailedError $e) {
				throw $e;
			} catch (Throwable $e) {
				// Correctly thrown exception
				$this->assertInstanceOf(InvalidArgumentException::class, $e);
			}
		}
	}

}
