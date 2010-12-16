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
 * Class for sending information to FirePHP.
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
	 * Type information.
	 *
	 * @var string
	 */
	const INFO = 'INFO';

	/**
	 * Type warning.
	 *
	 * @var string
	 */
	const WARNING = 'WARN';

	/**
	 * Type error.
	 *
	 * @var string
	 */
	const ERROR = 'ERROR';

	/**
	 * Type log.
	 *
	 * @var string
	 */
	const LOG = 'LOG';

	/**
	 * Type trace.
	 *
	 * @var string
	 */
	const TRACE = 'TRACE';

	/**
	 * Type table.
	 *
	 * @var string
	 */
	const TABLE = 'TABLE';

	/**
	 * Is logging enabled.
	 *
	 * @var bool
	 */
	private static $enabled = true;

	/**
	 * Sets if logging id enabled.
	 *
	 * @param boolean $flag Is logging enabled
	 */
	public static function setEnabled($flag = true)
	{
		self::$enabled = (bool) $flag;
	}

	/**
	 * Sends an information message.
	 *
	 * @param mixed $message Message text
	 * @param string $label Message label
	 * @return boolean
	 */
	public static function info($message, $label = '')
	{
		return self::log($message, $label, self::INFO);
	}

	/**
	 * Sends a warning.
	 *
	 * @param mixed $message Message text
	 * @param string $label Message label
	 * @return boolean
	 */
	public static function warning($message, $label = '')
	{
		return self::log($message, $label, self::WARNING);
	}

	/**
	 * Sends an error.
	 *
	 * @param mixed $message Message text
	 * @param string $label Message label
	 * @return boolean
	 */
	public static function error($message, $label = '')
	{
		return self::log($message, $label, self::ERROR);
	}

	/**
	 * Sends a log message.
	 *
	 * @param mixed $message Message text
	 * @param string $label Message label
	 * @param string $type Message type
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
	 * Sends a trace.
	 *
	 * @param string $message Message text
	 * @param string $file File name
	 * @param integer $line File line
	 * @param array $trace Trace
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
	 * Sends a table.
	 *
	 * @param string $label Message label
	 * @param array $header Table header
	 * @param array $data Table data
	 * @param string $ident Unique identifier
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
	 * Logs an exception.
	 *
	 * @param \Exception $e Exception to log
	 * @return boolean First exception sending result
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
	}

	/**
	 * Sends output.
	 *
	 * @param array $output Output
	 * @param string $ident Message identifier
	 * @return boolean
	 */
	private static function send(array $output, $ident = '')
	{
		// Headers were already sent, can not proceed
		if (headers_sent()) {
			return false;
		}

		// Logging is disabled
		if (!self::$enabled) {
			return false;
		}

		// Sending only if FirePHP is enabled
		if (!isset($_SERVER['HTTP_USER_AGENT']) || false === strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP/')) {
			return false;
		}

		// Sent headers count
		static $no = 0;

		// Adding filename and line where logging was called
		$first = reset($output);
		if (empty($first['File'])) {
			// Cut message information
			$first = array_shift($output);

			// Find file
			$backtrace = debug_backtrace();
			$hop = array_shift($backtrace);

			// Remove \Jyxo\FirePhp call
			while (__FILE__ === $hop['file']) {
				$hop = array_shift($backtrace);
			}

			// Add file information
			$first['File'] = $hop['file'];
			$first['Line'] = $hop['line'];

			// And return altered information back
			array_unshift($output, $first);
		}

		// Splitting result
		$parts = str_split(json_encode($output), 5000);

		// If an identifier was provided, delete previous messages with that identifier
		if (!empty($ident)) {
			static $idents = array();

			// Delete previous send
			if (isset($idents[$ident])) {
				for ($i = $idents[$ident][0]; $i <= $idents[$ident][1]; $i++) {
					header('X-Wf-Jyxo-1-1-Jyxo' . $i . ':');
				}
			}

			// Save identifiers of headers that will be actually used
			$idents[$ident] = array($no + 1, $no + count($parts));
		}

		// Sending
		header('X-Wf-Protocol-Jyxo: http://meta.wildfirehq.org/Protocol/JsonStream/0.2');
		header('X-Wf-Jyxo-Structure-1: http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1');
		header('X-Wf-Jyxo-Plugin-1: http://meta.firephp.org/Wildfire/Plugin/FirePHP/Library-FirePHPCore/0.3');
		foreach ($parts as $part) {
			$no++;
			header(sprintf('X-Wf-Jyxo-1-1-Jyxo%s: |%s|\\', $no, $part));
		}
		// Last one is sent again but without \
		header(sprintf('X-Wf-Jyxo-1-1-Jyxo%s: |%s|', $no, $part));

		return true;
	}

	/**
	 * Replaces objects with appropriate names in traces.
	 * Solves recursion problem in json_encode.
	 * Taken from Nette.
	 *
	 * @param mixed $value Variable to be replaced
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
