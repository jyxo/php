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

namespace Jyxo\Mail;

/**
 * List of possible content encodings.
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Encoding
{
	/**
	 * 8-bit encoding.
	 *
	 * @var string
	 */
	const BIT8 = '8bit';

	/**
	 * 7-bit encoding.
	 *
	 * @var string
	 */
	const BIT7 = '7bit';

	/**
	 * Binary encoding.
	 *
	 * @var string
	 */
	const BINARY = 'binary';

	/**
	 * Base64 encoding.
	 *
	 * @var string
	 */
	const BASE64 = 'base64';

	/**
	 * Quoted printable encoding.
	 *
	 * @var string
	 */
	const QUOTED_PRINTABLE = 'quoted-printable';

	/**
	 * Constructor preventing from creating instances.
	 *
	 * @throws \LogicException When trying to create an instance
	 */
	public final function __construct()
	{
		throw new \LogicException(sprintf('Cannot create an instance of a static class %s.', get_class($this)));
	}

	/**
	 * Checks if the given encoding is compatible.
	 *
	 * @param string $encoding Encoding name
	 * @return boolean
	 */
	public static function isCompatible($encoding)
	{
		static $encodings = [
			self::BIT7 => true,
			self::BIT8 => true,
			self::BINARY => true,
			self::BASE64 => true,
			self::QUOTED_PRINTABLE => true
		];

		return isset($encodings[$encoding]);
	}

	/**
	 * Encodes a string using the given encoding.
	 *
	 * @param string $string Input string
	 * @param string $encoding Encoding name
	 * @param integer $lineLength Line length
	 * @param string $lineEnd Line ending
	 * @return string
	 * @throws \InvalidArgumentException If an incompatible encoding was provided
	 */
	public static function encode($string, $encoding, $lineLength, $lineEnd)
	{
		switch ($encoding) {
			case self::BASE64:
				return self::encodeBase64($string, $lineLength, $lineEnd);
			case self::BIT7:
				// Break missing intentionally
			case self::BIT8:
				return \Jyxo\StringUtil::fixLineEnding(trim($string), $lineEnd) . $lineEnd;
			case self::QUOTED_PRINTABLE:
				return self::encodeQuotedPrintable($string, $lineLength, $lineEnd);
			case self::BINARY:
				return $string;
			default:
				throw new \InvalidArgumentException(sprintf('Incompatible encoding %s.', $encoding));
		}
	}

	/**
	 * Encodes a string using the quoted-printable encoding.
	 *
	 * @param string $string Input string
	 * @param integer $lineLength Line length
	 * @param string $lineEnd Line ending
	 * @return string
	 */
	private static function encodeQuotedPrintable($string, $lineLength, $lineEnd)
	{
		$encoded = \Jyxo\StringUtil::fixLineEnding(trim($string), $lineEnd);

		// Replaces all high ASCII characters, control codes and '='
		$encoded = preg_replace_callback('~([\000-\010\013\014\016-\037\075\177-\377])~', function($matches) {
			return '=' . sprintf('%02X', ord($matches[1]));
		}, $encoded);

		// Replaces tabs and spaces if on line ends
		$encoded = preg_replace_callback('~([\011\040])' . $lineEnd . '~', function($matches) use ($lineEnd) {
			return '=' . sprintf('%02X', ord($matches[1])) . $lineEnd;
		}, $encoded);

		$output = '';
		$lines = explode($lineEnd, $encoded);
		// Release from memory
		unset($encoded);
		foreach ($lines as $line) {
			// Line length is less than maximum
			if (strlen($line) <= $lineLength) {
				$output .= $line . $lineEnd;
				continue;
			}

			do {
				$partLength = strlen($line);
				if ($partLength > $lineLength) {
					$partLength = $lineLength;
				}

				// Cannot break a line in the middle of a character
				$pos = strrpos(substr($line, 0, $partLength), '=');
				if ((false !== $pos) && ($pos >= $partLength - 2)) {
					$partLength = $pos;
				}

				// If the last char is a break, move one character backwards
				if (($partLength > 0) && (' ' == $line[$partLength - 1])) {
					$partLength--;
				}

				// Saves string parts, trims the string and continues
				$output .= substr($line, 0, $partLength);
				$line = substr($line, $partLength);

				// We are in the middle of a line
				if (!empty($line)) {
					$output .= '=';
				}
				$output .= $lineEnd;
			} while (!empty($line));
		}

		return $output;
	}

	/**
	 * Encodes a string using the base64 encoding.
	 *
	 * @param string $string Input string
	 * @param integer $lineLength Line length
	 * @param string $lineEnd Line ending
	 * @return string
	 */
	private static function encodeBase64($string, $lineLength, $lineEnd)
	{
		return trim(chunk_split(base64_encode($string), $lineLength, $lineEnd));
	}
}
