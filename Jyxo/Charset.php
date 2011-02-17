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
 * Base class for common charset operations.
 *
 * @category Jyxo
 * @package Jyxo\Charset
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jan Tichý
 * @author Jaroslav Hanslík
 * @author Štěpán Svoboda
 */
class Charset
{
	/**
	 * Detects charset of a given string.
	 *
	 * @param string $string String to detect
	 * @return string
	 */
	public static function detect($string)
	{
		$charset = mb_detect_encoding($string, 'UTF-8, ISO-8859-2, ASCII, UTF-7, EUC-JP, SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');

		// The previous function can not handle WINDOWS-1250 and returns ISO-8859-2 instead
		if ('ISO-8859-2' === $charset && preg_match('~[\x7F-\x9F\xBC]~', $string)) {
			$charset = 'WINDOWS-1250';
		}

		return $charset;
	}

	/**
	 * Converts a string from various charsets to UTF-8.
	 *
	 * The charset is automatically detected if not given.
	 *
	 * @param string $string String to convert
	 * @param string $charset Actual charset
	 * @return string
	 */
	public static function convert2utf($string, $charset = '')
	{
		$charset = $charset ?: self::detect($string);
		// Detection sometimes fails or the string may be in wrong format, so we remove invalid UTF-8 letters
		return @iconv($charset, 'UTF-8//TRANSLIT//IGNORE', $string);
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
	 * Transliterates or removes unknown UTF-8 characters from a string.
	 *
	 * @param string $string String to fix
	 * @return string
	 */
	public static function fixUtf($string)
	{
		return @iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $string);
	}

}
