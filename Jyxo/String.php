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
 * Obecná třída pro nejčastější funkce používané prakticky ve všech aplikacích.
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
	 * Ořeže všechna slova v řetězci delší než požadovaná délka.
	 * Odděluje podle bílých znaků.
	 * Pokud dojde k ořezání, přidá "tři tečky" na konec slova. Ty jsou nad rámec požadované dělky.
	 *
	 * @param string $string
	 * @param integer $length
	 * @param string $etc
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
	 * Ořeže řetězec na požadovanou délku.
	 * Řeže podle celých slov (oddělovače jsou cokoliv, co není písmeno nebo číslo).
	 * Pokud dojde k ořezání, přidá "tři tečky" na konec. I s těmi se v limitu počítá.
	 *
	 * @param string $string
	 * @param integer $max
	 * @param string $etc
	 * @return string
	 */
	public static function cut($string, $max = 50, $etc = '...')
	{
		// Ořízneme mezery
		$string = trim($string);

		// Není třeba zkracovat
		if (mb_strlen($string) <= $max) {
			return $string;
		}

		// Zjistí délku "tří teček"
		switch ($etc) {
			case '&hellip;':
				$etcLength = 1;
				break;
			default:
				$etcLength = mb_strlen(html_entity_decode($etc));
				break;
		}

		// Hledáme hranici slov
		$search = mb_substr($string, 0, ($max - $etcLength) + 1);
		if (preg_match('~[^\w\pL\pN]~u', $search)) {
			// Nalezena hranice slova
			$string = preg_replace('~[^\w\pL\pN]*[\w\pL\pN]*$~uU', '', $search);
		} else {
			// Žádná hranice slova, je nutné oříznout natvrdo
			$string = mb_substr($string, 0, $max - $etcLength);
		}

		// Přidáme "tři tečky"
		$string .= $etc;

		return $string;
	}

	/**
	 * Převede řetězec z UTF-8 do ISO-8859-2.
	 *
	 * @param string $text
	 * @return string
	 */
	public static function utf2iso($text)
	{
		return iconv('UTF-8', 'ISO-8859-2//TRANSLIT', $text);
	}

	/**
	 * Převede řetězec z UTF-8 do identové podoby.
	 *
	 * @param string $text
	 * @return string
	 */
	public static function utf2ident($text)
	{
		// Převede nejprve na lower ascii, a pak vše mimo a-z a 0-9 na pomlčky
		$ident = preg_replace('~[^a-z0-9]~', '-', strtolower(self::utf2ascii($text)));
		// Odstraní násobné pomlčky a pomlčky z okrajů
		return trim(preg_replace('~-+~', '-', $ident), '-');
	}

	/**
	 * Převede řetězec z UTF-8 do ASCII.
	 *
	 * @param string $text
	 * @return string
	 */
	public static function utf2ascii($text)
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
		return strtr($text, $replace);
	}

	/**
	 * Fonetický přepis z azbuky do ASCII.
	 *
	 * @param string $string
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
	 * Převede text z CP-1250 do ASCII
	 *
	 * @param string $s
	 * @return string
	 */
	public static function win2ascii($s)
	{
		return strtr($s,
			"\xe1\xe4\xe8\xef\xe9\xec\xed\xbe\xe5\xf2\xf3\xf6\xf5\xf4\xf8\xe0\x9a\x9d\xfa\xf9\xfc\xfb\xfd\x9e"
			. "\xc1\xc4\xc8\xcf\xc9\xcc\xcd\xbc\xc5\xd2\xd3\xd6\xd5\xd4\xd8\xc0\x8a\x8d\xda\xd9\xdc\xdb\xdd\x8e",
			'aacdeeillnoooorrstuuuuyzAACDEEILLNOOOORRSTUUUUYZ'
		);
	}


	/**
	 * Převede text z ISO-8859-2 do ASCII
	 *
	 * @param string $s
	 * @return string
	 */
	public static function iso2ascii($s)
	{
		return strtr($s,
			"\xe1\xe4\xe8\xef\xe9\xec\xed\xb5\xe5\xf2\xf3\xf6\xf5\xf4\xf8\xe0\xb9\xbb\xfa\xf9\xfc\xfb\xfd\xbe"
			. "\xc1\xc4\xc8\xcf\xc9\xcc\xcd\xa5\xc5\xd2\xd3\xd6\xd5\xd4\xd8\xc0\xa9\xab\xda\xd9\xdc\xdb\xdd\xae",
			'aacdeeillnoooorrstuuuuyzAACDEEILLNOOOORRSTUUUUYZ'
		);
	}

	/**
	 * Vygeneruje crc shodné na 32 i 64 bitech.
	 *
	 * @param string $string
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
	 * Vygeneruje nahodný řetězec o zadané délce.
	 *
	 * @param integer $length
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
	 * Zkontroluje řetězec jestli je validní UTF-8.
	 *
	 * @param string $string
	 * @return boolean
	 */
	public static function checkUtf($string)
	{
		return (bool) preg_match('~~u', $string);
	}

	/**
	 * Přeloží, či odstraní UTF-8 neznámé znaky.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function fixUtf($string)
	{
		return @iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $string);
	}

	/**
	 * Opraví a sjednotí konce řádků v řetězci.
	 *
	 * @param string $string
	 * @param string $lineEnd
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
	 * Zakryje lehce e-mailovou adresu před roboty.
	 *
	 * @param string $email
	 * @param string $comment
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
	 * Převede první písmeno řetězce na malé.
	 * Správně funguje i pro české znaky.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function lcfirst($string)
	{
		return mb_strtolower(mb_substr($string, 0, 1)) . mb_substr($string, 1);
	}

	/**
	 * Alias k funkci htmlspecialchars, do které automaticky vyplňuje některé parametry.
	 *
	 * @param string $string
	 * @param integer $quoteStyle
	 * @param boolean $doubleEncode
	 * @return string
	 */
	public static function escape($string, $quoteStyle = ENT_QUOTES, $doubleEncode = false)
	{
		return @htmlspecialchars($string, (int) $quoteStyle, 'utf-8', (bool) $doubleEncode);
	}

	/**
	 * Převádí velikost zadanou v bytech na kB, MB, GB, TB či PB,
	 * zároveň přiřadí jednotky.
	 *
	 * @param float $size
	 * @param string $decimalPoint
	 * @param string $thousandSeparator
	 * @return string
	 */
	public static function formatBytes($size, $decimalPoint = ',', $thousandSeparator = ' ')
	{
		static $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		foreach ($units as $unit) {
			if ($size < 1024) {
				break;
			}
			$size = $size / 1024;
		}

		$decimals = ('B' === $unit) || ('kB' === $unit) ? 0 : 1;
		return number_format($size, $decimals, $decimalPoint, $thousandSeparator) . ' ' . $unit;
	}
}
