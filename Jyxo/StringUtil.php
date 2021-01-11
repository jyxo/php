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

use function crc32;
use function html_entity_decode;
use function htmlspecialchars;
use function mb_strlen;
use function mb_strtolower;
use function mb_substr;
use function mt_rand;
use function number_format;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function str_replace;
use function strlen;
use function substr;
use function trim;
use const ENT_COMPAT;
use const ENT_QUOTES;

/**
 * Base class for common string operations.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Tichý
 * @author Jakub Tománek
 * @author Jaroslav Hanslík
 */
class StringUtil
{

	/**
	 * Trims all words in a string longer than given length.
	 * String is delimited by whitespaces.
	 * If a word is trimmed, an "etc" is added at the end. Its length is also considered.
	 *
	 * @param string $string Processed string
	 * @param int $length Maximum word length
	 * @param string $etc "etc" definition
	 * @return string
	 */
	public static function cutWords(string $string, int $length = 25, string $etc = '...'): string
	{
		return preg_replace_callback('~[^\\s]{' . $length . ',}~', static function ($matches) use ($length, $etc) {
			return StringUtil::cut($matches[0], $length, $etc);
		}, $string);
	}

	/**
	 * Trims a string to given length.
	 * Trims at word boundaries (all non-alphanumeric characters are considered delimiters).
	 * If the given string is trimmed, an "etc" is added at the end. Its length is also considered.
	 *
	 * @param string $string Trimmed string
	 * @param int $max Maximum length
	 * @param string $etc "etc" definition
	 * @return string
	 */
	public static function cut(string $string, int $max = 50, string $etc = '...'): string
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
		// If no word boundary found, will trim in the middle of a word
		$search = mb_substr($string, 0, $max - $etcLength + 1, 'utf-8');
		$string = preg_match('~[^\\w\\pL\\pN]~u', $search) ? preg_replace('~[^\\w\\pL\\pN]*[\\w\\pL\\pN]*$~uU', '', $search) : mb_substr(
			$string,
			0,
			$max - $etcLength,
			'utf-8'
		);

		// Add "etc" at the end
		$string .= $etc;

		return $string;
	}

	/**
	 * Generates a crc checksum same on 32 and 64-bit platforms.
	 *
	 * @param string $string Input string
	 * @return int
	 */
	public static function crc(string $string): int
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
	 * @param int $length String length
	 * @return string
	 */
	public static function random(int $length): string
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
	public static function fixLineEnding(string $string, string $lineEnd = "\n"): string
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
	 * @param bool $comment Put a comment into the address
	 * @return string
	 */
	public static function obfuscateEmail(string $email, bool $comment = false): string
	{
		return $comment ? str_replace('@', '&#64;<!---->', $email) : str_replace('@', '&#64;', $email);
	}

	/**
	 * Converts first character of a string to lowercase.
	 * Works correctly with multibyte encodings.
	 *
	 * @param string $string Input string
	 * @return string
	 */
	public static function lcfirst(string $string): string
	{
		return mb_strtolower(mb_substr($string, 0, 1, 'utf-8')) . mb_substr($string, 1, mb_strlen($string, 'utf-8') - 1, 'utf-8');
	}

	/**
	 * Htmlspecialchars function alias with some parameters automatically set.
	 *
	 * @param string $string Input string
	 * @param int $quoteStyle Quote style
	 * @param bool $doubleEncode Prevent from double encoding
	 * @return string
	 */
	public static function escape(string $string, int $quoteStyle = ENT_QUOTES, bool $doubleEncode = false): string
	{
		return @htmlspecialchars($string, $quoteStyle, 'utf-8', $doubleEncode);
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
	public static function formatBytes(float $size, string $decimalPoint = ',', string $thousandsSeparator = ' '): string
	{
		static $units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
		foreach ($units as $unit) {
			if ($size < 1024) {
				break;
			}
			$size /= 1024;
		}

		$decimals = ($unit === 'B') || ($unit === 'kB') ? 0 : 1;

		return number_format($size, $decimals, $decimalPoint, $thousandsSeparator) . ' ' . $unit;
	}

}
