<?php

/**
 * Jyxo Library
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
 * @package Jyxo
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Tichý <libs@jyxo.com>
 * @author Jakub Tománek <libs@jyxo.com>
 * @author Jaroslav Hanslík <libs@jyxo.com>
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

		return preg_replace_callback('~[^\s]{' . $length . ',}~', function($matches) use ($length, $etc) {
			return \Jyxo\String::cut($matches[0], $length, $etc);
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
		if (mb_strlen($string) <= $max) {
			return $string;
		}

		// Find out "etc" length
		switch ($etc) {
			case '&hellip;':
				$etcLength = 1;
				break;
			default:
				$etcLength = mb_strlen(html_entity_decode($etc));
				break;
		}

		// Look for word boundaries
		$search = mb_substr($string, 0, ($max - $etcLength) + 1);
		if (preg_match('~[^\w\pL\pN]~u', $search)) {
			// Boundary found
			$string = preg_replace('~[^\w\pL\pN]*[\w\pL\pN]*$~uU', '', $search);
		} else {
			// No word boundary found, will trim in the middle of a word
			$string = mb_substr($string, 0, $max - $etcLength);
		}

		// Add "etc" at the end
		$string .= $etc;

		return $string;
	}

	/**
	 * Converts a string from UTF-8 to ISO-8859-2.
	 *
	 * @param string $string String to convert
	 * @return string
	 */
	public static function utf2iso($string)
	{
		return iconv('UTF-8', 'ISO-8859-2//TRANSLIT', $string);
	}

	/**
	 * Converts a string from UTF-8 to an identifier form.
	 *
	 * @param string $string String to convert
	 * @return string
	 */
	public static function utf2ident($string)
	{
		// Convert to lowercase ASCII and than all non-alphanumeric characters to dashes
		$ident = preg_replace('~[^a-z0-9]~', '-', strtolower(self::utf2ascii($string)));
		// Remove multiple dashes and dashes on boundaries
		return trim(preg_replace('~-+~', '-', $ident), '-');
	}

	/**
	 * Converts a string from UTF-8 to ASCII.
	 *
	 * @param string $string String to convert
	 * @return string
	 */
	public static function utf2ascii($string)
	{
		static $replace = array(
			'á' => 'a', 'Á' => 'A', 'ä' => 'a', 'Ä' => 'A', 'â' => 'a', 'Â' => 'A', 'ă' => 'a', 'Ă' => 'A', 'ą' => 'a', 'Ą' => 'A',
			'č' => 'c', 'Č' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ć' => 'c', 'Ć' => 'C', 'ď' => 'd', 'Ď' => 'D', 'đ' => 'd', 'Đ' => 'D',
			'é' => 'e', 'É' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ę' => 'e', 'Ę' => 'E', 'í' => 'i', 'Í' => 'I',
			'î' => 'i', 'Î' => 'I', 'ł' => 'l', 'Ł' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ĺ' => 'l', 'Ĺ' => 'L', 'ń' => 'n', 'Ń' => 'N',
			'ň' => 'n', 'Ň' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ö' => 'o', 'Ö' => 'O', 'ő' => 'o', 'Ő' => 'O',
			'o' => 'o', 'O' => 'O', 'ř' => 'r', 'Ř' => 'R', 'ŕ' => 'r', 'Ŕ' => 'R', 'š' => 's', 'Š' => 'S', 'ś' => 's', 'Ś' => 'S',
			'ş' => 's', 'Ş' => 'S', 'ť' => 't', 'Ť' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ů' => 'u', 'Ů' => 'U',
			'ü' => 'u', 'Ü' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ý' => 'y', 'Ý' => 'Y', 'ž' => 'z', 'Ž' => 'Z', 'ź' => 'z', 'Ź' => 'Z',
			'ż' => 'z', 'Ż' => 'Z', 'ß' => 'ss', 'å' => 'a', 'Å' => 'A'
		);
		return strtr($string, $replace);
	}

	/**
	 * Phonetical transcription of a Cyrillic string into ASCII.
	 *
	 * @param string $string String to convert
	 * @return string
	 */
	public static function russian2ascii($string)
	{
		static $russian = array(
			'КВ', 'кв', 'КС', 'кс', 'А', 'а', 'Б', 'б', 'Ц', 'ц', 'Д', 'д', 'Э', 'э', 'Е', 'е', 'Ф', 'ф', 'Г', 'г', 'Х', 'х',
			'И', 'и', 'Й', 'й', 'К', 'к', 'Л', 'л', 'М', 'м', 'Н', 'н', 'О', 'о', 'П', 'п', 'Р', 'р', 'С', 'с', 'Т', 'т', 'У',
			'у', 'В', 'в', 'В', 'в', 'Ы', 'ы', 'З', 'з', 'Ч', 'ч', 'Ш', 'ш', 'Щ', 'щ', 'Ж', 'ж', 'Я', 'я', 'Ю', 'ю', 'ъ', 'ь'
		);
		static $ascii = array(
			'Q', 'q', 'X', 'x', 'A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e', 'E', 'e', 'F', 'f', 'G', 'g', 'H', 'h', 'I',
			'i', 'J', 'j', 'K', 'k', 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o', 'P', 'p', 'R', 'r', 'S', 's', 'T', 't', 'U', 'u',
			'V', 'v', 'W', 'w', 'Y', 'y', 'Z', 'z', 'Ch', 'ch', 'Sh', 'sh', 'Sht', 'sht', 'Zh', 'zh', 'Ja', 'ja', 'Ju', 'ju'
		);
		return str_replace($russian, $ascii, $string);
	}

	/**
	 * Converts a string from CP-1250 to ASCII.
	 *
	 * @param string $string String to convert
	 * @return string
	 */
	public static function win2ascii($string)
	{
		return strtr($string,
			"\xe1\xe4\xe8\xef\xe9\xec\xed\xbe\xe5\xf2\xf3\xf6\xf5\xf4\xf8\xe0\x9a\x9d\xfa\xf9\xfc\xfb\xfd\x9e"
			. "\xc1\xc4\xc8\xcf\xc9\xcc\xcd\xbc\xc5\xd2\xd3\xd6\xd5\xd4\xd8\xc0\x8a\x8d\xda\xd9\xdc\xdb\xdd\x8e",
			'aacdeeillnoooorrstuuuuyzAACDEEILLNOOOORRSTUUUUYZ'
		);
	}


	/**
	 * Converts a string from ISO-8859-2 to ASCII.
	 *
	 * @param string $string String to convert
	 * @return string
	 */
	public static function iso2ascii($string)
	{
		return strtr($string,
			"\xe1\xe4\xe8\xef\xe9\xec\xed\xb5\xe5\xf2\xf3\xf6\xf5\xf4\xf8\xe0\xb9\xbb\xfa\xf9\xfc\xfb\xfd\xbe"
			. "\xc1\xc4\xc8\xcf\xc9\xcc\xcd\xa5\xc5\xd2\xd3\xd6\xd5\xd4\xd8\xc0\xa9\xab\xda\xd9\xdc\xdb\xdd\xae",
			'aacdeeillnoooorrstuuuuyzAACDEEILLNOOOORRSTUUUUYZ'
		);
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
	 * Checks if the given string is valid UTF-8.
	 *
	 * @param string $string String to check
	 * @return boolean
	 */
	public static function checkUtf($string)
	{
		return (bool) preg_match('~~u', $string);
	}

	/**
	 * Transliterates or removes unknown UTF-8 characters from a string.
	 *
	 * @param string $string String to fix
	 * @return string
	 */
	public static function fixUtf($string)
	{
		return @iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $string);
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
		return mb_strtolower(mb_substr($string, 0, 1)) . mb_substr($string, 1);
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
