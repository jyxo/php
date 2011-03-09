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
 * Class for sending information to FirePHP.
 *
 * @category Jyxo
 * @package Jyxo\FirePhp
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
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
	 * @var boolean
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
	 * Dumps a variable.
	 *
	 * @param mixed $variable Variable
	 * @param string $label Variable label
	 * @return boolean
	 */
	public static function dump($variable, $label = '')
	{
		return self::log($variable, (string) $label);
	}

	/**
	 * Sends an information message.
	 *
	 * @param string $message Message text
	 * @param string $label Message label
	 * @return boolean
	 */
	public static function info($message, $label = '')
	{
		return self::log((string) $message, (string) $label, self::INFO);
	}

	/**
	 * Sends a warning.
	 *
	 * @param string $message Message text
	 * @param string $label Message label
	 * @return boolean
	 */
	public static function warning($message, $label = '')
	{
		return self::log((string) $message, (string) $label, self::WARNING);
	}

	/**
	 * Sends an error.
	 *
	 * @param string $message Message text
	 * @param string $label Message label
	 * @return boolean
	 */
	public static function error($message, $label = '')
	{
		return self::log((string) $message, (string) $label, self::ERROR);
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
			self::encodeVariable($message)
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
				'Message' => Charset::fixUtf($message),
				'File' => $file,
				'Line' => $line,
				'Trace' => self::replaceVariable($trace)
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

		// Sending only if FirePHP is installed
		if (!self::isInstalled()) {
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
					header_remove('X-Wf-Jyxo-1-1-Jyxo' . $i);
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
	 * Checks if FirePHP extension is installed.
	 *
	 * @return boolean
	 */
	private static function isInstalled()
	{
		// Header X-FirePHP-Version
		if (isset($_SERVER['HTTP_X_FIREPHP_VERSION'])) {
			return true;
		}

		// Header x-insight
		if (isset($_SERVER['HTTP_X_INSIGHT']) && 'activate' === $_SERVER['HTTP_X_INSIGHT']) {
			return true;
		}

		// Modified user-agent
		if (isset($_SERVER['HTTP_USER_AGENT']) && false !== strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP/')) {
			return true;
		}

		// Not installed
		return false;
	}

	/**
	 * Replaces objects with appropriate names in variable.
	 *
	 * @param mixed $variable Variable where to replace objects
	 * @return mixed
	 */
	private static function replaceVariable($variable)
	{
		if (is_object($variable)) {
			return 'object ' . get_class($variable);
		} elseif (is_resource($variable)) {
			return (string) $variable;
		} elseif (is_array($variable)) {
			foreach ($variable as $k => $v) {
				unset($variable[$k]);
				$variable[$k] = self::replaceVariable($v);
			}
			return $variable;
		} else {
			return $variable;
		}
	}

	/**
	 * Encodes a variable.
	 *
	 * @param mixed $variable Variable to be encoded
	 * @param integer $objectDepth Current object traversal depth
	 * @param integer $arrayDepth Current array traversal depth
	 * @param integer $totalDepth Current total traversal depth
	 * @return array
	 */
	private static function encodeVariable($variable, $objectDepth = 1, $arrayDepth = 1, $totalDepth = 1)
	{
		static $maxObjectDepth = 5;
		static $maxArrayDepth = 5;
		static $maxTotalDepth = 10;
		static $stack = array();

		if ($totalDepth > $maxTotalDepth) {
			return sprintf('** Max Depth (%s) **', $maxTotalDepth);
		}

		if (is_resource($variable)) {
			return sprintf('** %s **', (string) $variable);
		} elseif (is_object($variable)) {
			if ($objectDepth > $maxObjectDepth) {
				return sprintf('** Max Object Depth (%s) **', $maxObjectDepth);
			}

			$class = get_class($variable);

			// Check recursion
			foreach ($stack as $item) {
				if ($item === $variable) {
					return sprintf('** Recursion (%s) **', $class);
				}
			}
			array_push($stack, $variable);

			// Add class name
			$return = array('__className' => $class);

			// Add properties
			$reflectionClass = new \ReflectionClass($class);
			foreach ($reflectionClass->getProperties() as $property) {
				$name = $property->getName();
				$rawName = $name;

				if ($property->isStatic()) {
					$name = 'static:' . $name;
				}

				if ($property->isPublic()) {
					$name = 'public:' . $name;
				} elseif ($property->isProtected()) {
					$name = 'protected:' . $name;
					$rawName = "\0" . '*' . "\0" . $rawName;
				} elseif ($property->isPrivate()) {
					$name = 'private:' . $name;
					$rawName = "\0" . $class . "\0" . $rawName;
				}

				if (!$property->isPublic()) {
					$property->setAccessible(true);
				}
				$return[$name] = self::encodeVariable($property->getValue($variable), $objectDepth + 1, 1, $totalDepth + 1);
			}

			// Add members that are not defined in the class but exist in the object
			$members = (array) $variable;
			foreach ($members as $rawName => $member) {
				$name = $rawName;
				if ("\0" === $name[0]) {
					$parts = explode("\0", $name);
					$name = $parts[2];
				}
				if (!$reflectionClass->hasProperty($name)) {
					$name = 'undeclared:' . $name;
					$return[$name] = self::encodeVariable($member, $objectDepth + 1, 1, $totalDepth + 1);
				}
			}
			unset($members);

			array_pop($stack);

			return $return;
		} elseif (is_array($variable)) {
			if ($arrayDepth > $maxArrayDepth) {
				return sprintf('** Max Array Depth (%s) **', $maxArrayDepth);
			}

			$return = array();
			foreach ($variable as $k => $v) {
				// Encoding the $GLOBALS PHP array causes an infinite loop as it contains a reference to itself
				if ('GLOBALS' === $k && is_array($v) && array_key_exists('GLOBALS', $v)) {
					$v['GLOBALS'] = '** Recursion (GLOBALS) **';
				}
				$return[$k] = self::encodeVariable($v, 1, $arrayDepth + 1, $totalDepth + 1);
			}

			return $return;
		} else {
			return $variable;
		}
	}
}
