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
 * Base class for common string operations.
 *
 * @category Jyxo
 * @package Jyxo\String
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Tichý
 * @author Jakub Tománek
 * @author Jaroslav Hanslík
 */
class String
{
	/**
	 * Trims all words in a string longer than given length.
	 * String is delimited by whitespaces.
	 * If a word is trimmed, an "etc" is added at the end. Its length is also considered.
	 *
	 * @param string $string Processed string
	 * @param integer $length Maximum word length
	 * @param string $etc "etc" definition
	 * @return string
	 */
	public static function cutWords($string, $length = 25, $etc = '...')
	{
		$length = (int) $length;

		return preg_replace_callback('~[^\\s]{' . $length . ',}~', function($matches) use ($length, $etc) {
			return String::cut($matches[0], $length, $etc);
		}, $string);
	}

	/**
	 * Trims a string to given length.
	 * Trims at word boundaries (all non-alphanumeric characters are considered delimiters).
	 * If the given string is trimmed, an "etc" is added at the end. Its length is also considered.
	 *
	 * @param string $string Trimmed string
	 * @param integer $max Maximum length
	 * @param string $etc "etc" definition
	 * @return string
	 */
	public static function cut($string, $max = 50, $etc = '...')
	{
		// Trim whitespace
		$string = trim($string);

		// No trimming is needed
		if (mb_strlen($string, 'utf-8') <= $max) {
			return $string;
		}

		// Find out "etc" length
		switch ($etc) {
			case '&hellip;':
				$etcLength = 1;
				break;
			default:
				$etcLength = mb_strlen(html_entity_decode($etc, ENT_COMPAT, 'utf-8'), 'utf-8');
				break;
		}

		// Look for word boundaries
		$search = mb_substr($string, 0, ($max - $etcLength) + 1, 'utf-8');
		if (preg_match('~[^\\w\\pL\\pN]~u', $search)) {
			// Boundary found
			$string = preg_replace('~[^\\w\\pL\\pN]*[\\w\\pL\\pN]*$~uU', '', $search);
		} else {
			// No word boundary found, will trim in the middle of a word
			$string = mb_substr($string, 0, $max - $etcLength, 'utf-8');
		}

		// Add "etc" at the end
		$string .= $etc;

		return $string;
	}

	/**
	 * Generates a crc checksum same on 32 and 64-bit platforms.
	 *
	 * @param string $string Input string
	 * @return integer
	 */
	public static function crc($string)
	{
		$crc = crc32($string);
		if ($crc & 0x80000000) {
			$crc ^= 0xffffffff;
			$crc++;
			$crc = -$crc;
		}
		return $crc;
	}

	/**
	 * Generates a random string of given length.
	 *
	 * @param integer $length String length
	 * @return string
	 */
	public static function random($length)
	{
		static $chars = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$random = '';
		for ($i = 1; $i <= $length; $i++) {
			$random .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $random;
	}

	/**
	 * Fixes and unifies line endings in a string.
	 *
	 * @param string $string String to fix
	 * @param string $lineEnd Desired line ending
	 * @return string
	 */
	public static function fixLineEnding($string, $lineEnd = "\n")
	{
		$string = str_replace("\r\n", "\n", $string);
		$string = str_replace("\r", "\n", $string);
		$string = str_replace("\n", $lineEnd, $string);

		return $string;
	}

	/**
	 * Obfuscates an email address.
	 *
	 * @param string $email Email address
	 * @param string $comment Put a comment into the address
	 * @return string
	 */
	public static function obfuscateEmail($email, $comment = false)
	{
		if ($comment) {
			return str_replace('@', '&#64;<!---->', $email);
		} else {
			return str_replace('@', '&#64;', $email);
		}
	}

	/**
	 * Converts first character of a string to lowercase.
	 * Works correctly with multibyte encodings.
	 *
	 * @param string $string Input string
	 * @return string
	 */
	public static function lcfirst($string)
	{
		return mb_strtolower(mb_substr($string, 0, 1, 'utf-8')) . mb_substr($string, 1, mb_strlen($string, 'utf-8') - 1, 'utf-8');
	}

	/**
	 * Htmlspecialchars function alias with some parameters automatically set.
	 *
	 * @param string $string Input string
	 * @param integer $quoteStyle Quote style
	 * @param boolean $doubleEncode Prevent from double encoding
	 * @return string
	 */
	public static function escape($string, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
	{
		return @htmlspecialchars($string, (int) $quoteStyle, 'utf-8', (bool) $doubleEncode);
	}

	/**
	 * Converts given size in bytes to kB, MB, GB, TB or PB
	 * and appends the appropriate unit.
	 *
	 * @param float $size Input size
	 * @param string $decimalPoint Decimal point
	 * @param string $thousandsSeparator Thousands separator
	 * @return string
	 */
	public static function formatBytes($size, $decimalPoint = ',', $thousandsSeparator = ' ')
	{
		static $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		foreach ($units as $unit) {
			if ($size < 1024) {
				break;
			}
			$size = $size / 1024;
		}

		$decimals = ('B' === $unit) || ('kB' === $unit) ? 0 : 1;
		return number_format($size, $decimals, $decimalPoint, $thousandsSeparator) . ' ' . $unit;
	}
}
