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
 * Třída na odesílání informací do FirePHP.
 *
 * @category Jyxo
 * @package Jyxo
 * @copyright Copyright (c) 2005-2010 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class FirePhp
{
	/**
	 * Typ informační zpráva.
	 *
	 * @var string
	 */
	const INFO = 'INFO';

	/**
	 * Typ varování.
	 *
	 * @var string
	 */
	const WARNING = 'WARN';

	/**
	 * Typ chyba.
	 *
	 * @var string
	 */
	const ERROR = 'ERROR';

	/**
	 * Typ log.
	 *
	 * @var string
	 */
	const LOG = 'LOG';

	/**
	 * Typ trasování.
	 *
	 * @var string
	 */
	const TRACE = 'TRACE';

	/**
	 * Typ tabulka.
	 *
	 * @var string
	 */
	const TABLE = 'TABLE';

	/**
	 * Zda je logování povoleno.
	 *
	 * @var bool
	 */
	private static $enabled = true;

	/**
	 * Nastavuje, zda je logování povoleno.
	 *
	 * @param bool $flag
	 */
	public static function setEnabled($flag = true)
	{
		self::$enabled = (bool) $flag;
	} // setEnabled();

	/**
	 * Odešle informační zprávu.
	 *
	 * @param mixed $message
	 * @param string $label
	 * @return boolean
	 */
	public static function info($message, $label = '')
	{
		return self::log($message, $label, self::INFO);
	}

	/**
	 * Odešle varování.
	 *
	 * @param mixed $message
	 * @param string $label
	 * @return boolean
	 */
	public static function warning($message, $label = '')
	{
		return self::log($message, $label, self::WARNING);
	}

	/**
	 * Odešle chybu.
	 *
	 * @param mixed $message
	 * @param string $label
	 * @return boolean
	 */
	public static function error($message, $label = '')
	{
		return self::log($message, $label, self::ERROR);
	}

	/**
	 * Odešle log.
	 *
	 * @param mixed $message
	 * @param string $label
	 * @param string $type
	 * @return boolean
	 */
	public static function log($message, $label = '', $type = self::LOG)
	{
		$output = array(
			array(
				'Type' => $type,
				'Label' => $label
			),
			self::replaceObjects($message)
		);

		return self::send($output);
	}

	/**
	 * Odešle trasování.
	 *
	 * @param string $message Zpráva
	 * @param string $file Soubor
	 * @param integer $line Řádka
	 * @param array $trace Trasování
	 * @return boolean
	 */
	public static function trace($message, $file, $line, array $trace)
	{
		$output = array(
			array(
				'Type' => self::TRACE,
				'Label' => null
			),
			array(
				'Message' => @iconv('utf-8', 'utf-8//IGNORE', $message),
				'File' => $file,
				'Line' => $line,
				'Trace' => self::replaceObjects($trace)
			)
		);

		return self::send($output);
	}

	/**
	 * Odešle tabulku.
	 *
	 * @param string $label Popisek
	 * @param array $header Hlavička
	 * @param array $data Data
	 * @param string $ident Jedinečný identifikátor
	 * @return boolean
	 */
	public static function table($label, array $header, array $data, $ident = '')
	{
		$output = array(
			array(
				'Type' => self::TABLE,
				'Label' => $label
			),
			array_merge(array($header), $data)
		);

		return self::send($output, $ident);
	}

	/**
	 * Loguje výjimku.
	 *
	 * @param \Exception $e
	 * @return boolean výsledek odeslání první výjimky
	 */
	public static function exception(\Exception $e)
	{
		$result = self::trace(
			'Exception: ' . $e->getMessage() . ' [' . $e->getCode() . ']',
			$e->getFile(),
			$e->getLine(),
			$e->getTrace()
		);
		while ($e = $e->getPrevious()) {
			self::trace(
				'Previous exception: ' . $e->getMessage() . ' [' . $e->getCode() . ']',
				$e->getFile(),
				$e->getLine(),
				$e->getTrace()
			);
		}
		return $result;
	} // exception();

	/**
	 * Odešle výstup.
	 *
	 * @param array $output
	 * @param string $ident
	 * @return boolean
	 */
	private static function send(array $output, $ident = '')
	{
		// Hlavičky byly odeslány, nelze poslat
		if (headers_sent()) {
			return false;
		}

		// Logování je zakázáno v aplikaci
		if (!self::$enabled) {
			return false;
		}

		// Posíláme, pouze pokud je FirePHP povoleno
		if (!isset($_SERVER['HTTP_USER_AGENT']) || false === strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP/')) {
			return false;
		}

		// Čítač poslaných hlaviček
		static $no = 0;

		// Doplnění souboru a řádku, odkud se loguje
		$first = reset($output);
		if (empty($first['File'])) {
			// Ukradneme si informace o zprávě
			$first = array_shift($output);

			// Najdeme soubor
			$backtrace = debug_backtrace();
			$hop = array_shift($backtrace);

			// Odstraníme volání \Jyxo\FirePhp
			while (__FILE__ === $hop['file']) {
				$hop = array_shift($backtrace);
			}

			// Doplníme info o souboru
			$first['File'] = $hop['file'];
			$first['Line'] = $hop['line'];

			// A vracíme doplněné informace zpět
			array_unshift($output, $first);
		}

		// Rozdělení výstupu
		$parts = str_split(json_encode($output), 5000);

		// Pokud je zadán identifikátor, smažeme předchozí odeslání stejného výstupu
		if (!empty($ident)) {
			static $idents = array();

			// Promazání předchozího odeslání
			if (isset($idents[$ident])) {
				for ($i = $idents[$ident][0]; $i <= $idents[$ident][1]; $i++) {
					header('X-Wf-Jyxo-1-1-Jyxo' . $i . ':');
				}
			}

			// Uložíme si čísla hlaviček, která budou použita
			$idents[$ident] = array($no + 1, $no + count($parts));
		}

		// Odeslání
		header('X-Wf-Protocol-Jyxo: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
		header('X-Wf-Jyxo-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
		header('X-Wf-Jyxo-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3');
		foreach ($parts as $part) {
			$no++;
			header(sprintf('X-Wf-Jyxo-1-1-Jyxo%s: |%s|\\', $no, $part));
		}
		// Poslední se pošle znovu, ale bez \
		header(sprintf('X-Wf-Jyxo-1-1-Jyxo%s: |%s|', $no, $part));

		return true;
	}

	/**
	 * Nahrazuje v trasování objekty za jejich názvy.
	 * Řeší problém rekurze v json_encode.
	 * Převzato z Nette.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	private static function replaceObjects($value)
	{
		if (is_object($value)) {
			return 'object ' . get_class($value);
		} elseif (is_resource($value)) {
			return (string) $value;
		} elseif (is_array($value)) {
			foreach ($value as $k => $v) {
				unset($value[$k]);
				$value[$k] = self::replaceObjects($v);
			}
		}

		return $value;
	}
}
