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
use function dechex;
use function floor;
use function hexdec;
use function is_array;
use function is_int;
use function is_string;
use function ltrim;
use function min;
use function preg_match;
use function sprintf;
use function str_pad;
use function strlen;
use const STR_PAD_LEFT;

/**
 * Class representing a RGB color.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Roman Řáha
 */
class Color
{

	/**
	 * Minimal luminance.
	 */
	public const LUM_MIN = 0x00;

	/**
	 * Maximal luminance.
	 */
	public const LUM_MAX = 0xFF;

	/**
	 * The red component of the color.
	 *
	 * @var int
	 */
	private $red = self::LUM_MIN;

	/**
	 * The green component of the color.
	 *
	 * @var int
	 */
	private $green = self::LUM_MIN;

	/**
	 * The blue component of the color.
	 *
	 * @var int
	 */
	private $blue = self::LUM_MIN;

	/**
	 * Constructor.
	 *
	 * Accepts the following definition formats:
	 * * Hexadecimal string (with or without the hashmark at the beginning)
	 * * Array with red, green and blue component luminance values (in this order)
	 * * Number,
	 * * {@link \Jyxo\Color},
	 * * null
	 *
	 * @param mixed $color Color definition
	 */
	public function __construct($color = null)
	{
		if (is_string($color)) {
			$this->initFromHex($color);
		} elseif (is_array($color)) {
			$this->initFromRgb($color);
		} elseif (is_int($color)) {
			$this->initFromInt($color);
		} elseif ($color instanceof self) {
			$this->red = $color->getRed();
			$this->green = $color->getGreen();
			$this->blue = $color->getBlue();
		}
	}

	/**
	 * Returns an inverse color.
	 *
	 * @return Color
	 */
	public function toInverse(): self
	{
		$negative = new self();
		// Subtracts color component values from the maximum luminance
		$negative->setRed(self::LUM_MAX - $this->red)
			->setGreen(self::LUM_MAX - $this->green)
			->setBlue(self::LUM_MAX - $this->blue);

		return $negative;
	}

	/**
	 * Returns the currect color converted to grayscale.
	 *
	 * @return Color
	 */
	public function toGrayScale(): self
	{
		$gray = new self();
		$gray->setLuminance($this->getLuminance());

		return $gray;
	}

	/**
	 * Returns the current color as an array of the red, green and blue components.
	 *
	 * @return array
	 */
	public function getRgb(): array
	{
		return [
			$this->red,
			$this->green,
			$this->blue,
		];
	}

	/**
	 * Returns the current color as a six-digit hexadecimal number.
	 *
	 * @return string
	 */
	public function getHex(): string
	{
		return str_pad(dechex($this->red), 2, '0', STR_PAD_LEFT)
			. str_pad(dechex($this->green), 2, '0', STR_PAD_LEFT)
			. str_pad(dechex($this->blue), 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Returns the current color in binary form 0 - black, 1 - white).
	 *
	 * @return int
	 */
	public function getBinary(): int
	{
		// Black or white corresponds to the most significant bit value
		$luminance = $this->getLuminance();

		return $luminance >> 7;
	}

	/**
	 * Returns the red component luminance.
	 *
	 * @return int
	 */
	public function getRed(): int
	{
		return $this->red;
	}

	/**
	 * Sets the red component luminance.
	 *
	 * @param int|string $red Component luminance
	 * @return Color
	 */
	public function setRed($red): self
	{
		$this->red = $this->toInt($red);

		return $this;
	}

	/**
	 * Returns the green component luminance.
	 *
	 * @return int
	 */
	public function getGreen(): int
	{
		return $this->green;
	}

	/**
	 * Sets the green component luminance.
	 *
	 * @param int|string $green Component luminance
	 * @return Color
	 */
	public function setGreen($green): self
	{
		$this->green = $this->toInt($green);

		return $this;
	}

	/**
	 * Returns the blue component luminance.
	 *
	 * @return int
	 */
	public function getBlue(): int
	{
		return $this->blue;
	}

	/**
	 * Sets the blue component luminance.
	 *
	 * @param int|string $blue Component luminance
	 * @return Color
	 */
	public function setBlue($blue): self
	{
		$this->blue = $this->toInt($blue);

		return $this;
	}

	/**
	 * Returns the color luminance according to the human perception.
	 *
	 * @return int
	 */
	public function getLuminance(): int
	{
		$luminance = 0.11 * $this->red + 0.59 * $this->green + 0.3 * $this->blue;

		return (int) floor($luminance);
	}

	/**
	 * Sets the color in grayscale according to the luminance value.
	 *
	 * @param int|string $luminance Luminance
	 * @return Color
	 */
	public function setLuminance($luminance): self
	{
		$luminance = $this->toInt($luminance);
		$this->red = $luminance;
		$this->green = $luminance;
		$this->blue = $luminance;

		return $this;
	}

	/**
	 * Sets color components using a hexadecimal RRGGBB string.
	 *
	 * @param string $hex Color definition
	 * @return Color
	 */
	private function initFromHex(string $hex): self
	{
		// Trim the hashmark if present
		$hex = ltrim($hex, '#');

		if (strlen($hex) === 3) {
			// RGB format support
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		if (!preg_match('~[a-f0-9]{6}~i', $hex)) {
			// Invalid color definition
			throw new InvalidArgumentException(sprintf('"%s" in not a valid hexadecimal color definition', $hex));
		}

		$this->initFromInt(hexdec($hex));

		return $this;
	}

	/**
	 * Sets color components from an array.
	 *
	 * @param array $rgb Color definition
	 * @return Color
	 */
	private function initFromRgb(array $rgb): self
	{
		$this->setRed($rgb[0])
			->setGreen($rgb[1])
			->setBlue($rgb[2]);

		return $this;
	}

	/**
	 * Sets color components from an integer.
	 *
	 * @param int $int Color definition
	 * @return Color
	 */
	private function initFromInt(int $int): self
	{
		$int = min([$int, 0xFFFFFF]);
		$this->red = self::LUM_MAX & ($int >> 16);
		$this->green = self::LUM_MAX & ($int >> 8);
		$this->blue = self::LUM_MAX & $int;

		return $this;
	}

	/**
	 * Returns the color luminance as a decimal integer.
	 *
	 * @param int|string $value Luminance value
	 * @return int
	 */
	private function toInt($value): int
	{
		if (is_string($value)) {
			$value = hexdec($value);
		}

		// Luminance must not be greater than 0xFF
		return min([(int) $value, self::LUM_MAX]);
	}

	/**
	 * Returns textual (#RRGGBB) representation of the current color.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return '#' . $this->getHex();
	}

}
