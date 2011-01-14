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
 * Class representing a RGB color.
 *
 * @category Jyxo
 * @package \Jyxo\Color
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Roman Řáha
 */
class Color
{
	/**
	 * Minimal luminance.
	 *
	 * @var integer
	 */
	const LUM_MIN = 0x00;

	/**
	 * Maximal luminance.
	 *
	 * @var integer
	 */
	const LUM_MAX = 0xFF;

	/**
	 * The red component of the color.
	 *
	 * @var integer
	 */
	private $red = self::LUM_MIN;

	/**
	 * The green component of the color.
	 *
	 * @var integer
	 */
	private $green = self::LUM_MIN;

	/**
	 * The blue component of the color.
	 *
	 * @var integer
	 */
	private $blue = self::LUM_MIN;

	/**
	 * Constructor.
	 *
	 * Accepts the following definition formats:
	 *  - Hexadecimal string (with or without the hashmark at the beginning)
	 *  - Array with red, green and blue component luminance values (in this order)
	 *  - Number,
	 *  - \Jyxo\Color,
	 *  - null
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
	 * @return \Jyxo\Color
	 */
	public function toInverse()
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
	 * @return \Jyxo\Color
	 */
	public function toGrayScale()
	{
		$gray = new self();
		$gray->setLuminance($this->getLuminance());
		return $gray;
	}

	/**
	 * Returns textual (#RRGGBB) representation of the current color.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return '#' . $this->getHex();
	}

	/**
	 * Returns the current color as an array of the red, green and blue components.
	 *
	 * @return array
	 */
	public function getRgb()
	{
		return array(
			$this->red,
			$this->green,
			$this->blue
		);
	}

	/**
	 * Returns the current color as a six-digit hexadecimal number.
	 *
	 * @return string
	 */
	public function getHex()
	{
		return str_pad(dechex($this->red), 2, '0', STR_PAD_LEFT)
			. str_pad(dechex($this->green), 2, '0', STR_PAD_LEFT)
			. str_pad(dechex($this->blue), 2, '0', STR_PAD_LEFT);
	}

	/**
	 * Returns the current color in binary form 0 - black, 1 - white).
	 *
	 * @return integer
	 */
	public function getBinary()
	{
		// Black or white corresponds to the most significant bit value
		$luminance = $this->getLuminance();
		return $luminance >> 7;
	}

	/**
	 * Returns the red component luminance.
	 *
	 * @return integer
	 */
	public function getRed()
	{
		return $this->red;
	}

	/**
	 * Sets the red component luminance.
	 *
	 * @param integer|string $red Component luminance
	 * @return \Jyxo\Color
	 */
	public function setRed($red)
	{
		$this->red = $this->toInt($red);
		return $this;
	}

	/**
	 * Returns the green component luminance.
	 *
	 * @return integer
	 */
	public function getGreen()
	{
		return $this->green;
	}

	/**
	 * Sets the green component luminance.
	 *
	 * @param integer|string $green Component luminance
	 * @return \Jyxo\Color
	 */
	public function setGreen($green)
	{
		$this->green = $this->toInt($green);
		return $this;
	}

	/**
	 * Returns the blue component luminance.
	 *
	 * @return integer
	 */
	public function getBlue()
	{
		return $this->blue;
	}

	/**
	 * Sets the blue component luminance.
	 *
	 * @param integer|string $blue Component luminance
	 * @return \Jyxo\Color
	 */
	public function setBlue($blue)
	{
		$this->blue = $this->toInt($blue);
		return $this;
	}

	/**
	 * Returns the color luminance according to the human perception.
	 *
	 * @return integer
	 */
	public function getLuminance()
	{
		$luminance = 0.11 * $this->red + 0.59 * $this->green + 0.3 * $this->blue;
		return (integer) floor($luminance);
	}

	/**
	 * Sets the color in grayscale according to the luminance value.
	 *
	 * @param integer|string $luminance Luminance
	 * @return \Jyxo\Color
	 */
	public function setLuminance($luminance)
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
	 * @return \Jyxo\Color
	 * @throws \InvalidArgumentException If an invalid hexadecimal definition was provided
	 */
	private function initFromHex($hex)
	{
		// Trim the hashmark if present
		$hex = ltrim($hex, '#');

		if (strlen($hex) == 3) {
			// RGB format support
			$hex = $hex{0} . $hex{0} . $hex{1} . $hex{1} . $hex{2} . $hex{2};
		}

		if (!preg_match('~[a-f0-9]{6}~i', $hex)) {
			// Invalid color definition
			throw new \InvalidArgumentException(sprintf('"%s" in not a valid hexadecimal color definition', $hex));
		}

		$this->initFromInt(hexdec($hex));
		return $this;
	}

	/**
	 * Sets color components from an array.
	 *
	 * @param array $rgb Color definition
	 * @return \Jyxo\Color
	 */
	private function initFromRgb(array $rgb)
	{
		$this->setRed($rgb[0])
			->setGreen($rgb[1])
			->setBlue($rgb[2]);
		return $this;
	}

	/**
	 * Sets color components from an integer.
	 *
	 * @param integer $int Color definition
	 * @return \Jyxo\Color
	 */
	private function initFromInt($int)
	{
		$int = min(array($int, 0xFFFFFF));
		$this->red = self::LUM_MAX & ($int >> 16);
		$this->green = self::LUM_MAX & ($int >> 8);
		$this->blue = self::LUM_MAX & $int;
		return $this;
	}

	/**
	 * Returns the color luminance as a decimal integer.
	 *
	 * @param integer|string $value Luminance value
	 * @return integer
	 */
	private function toInt($value)
	{
		if (is_string($value)) {
			$value = hexdec($value);
		}
		// Luminance must not be greater than 0xFF
		return min(array((integer) $value, self::LUM_MAX));
	}
}
