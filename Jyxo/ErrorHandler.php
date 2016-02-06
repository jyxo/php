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
 * Error and exception handler.
 *
 * @category Jyxo
 * @package Jyxo\ErrorHandling
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class ErrorHandler
{
	/**
	 * Notice.
	 *
	 * @var string
	 */
	const NOTICE = 'notice';

	/**
	 * Warning.
	 *
	 * @var string
	 */
	const WARNING = 'warning';

	/**
	 * Error.
	 *
	 * @var string
	 */
	const ERROR = 'error';

	/**
	 * Fatal error.
	 *
	 * @var string
	 */
	const FATAL = 'fatal';

	/**
	 * Exception.
	 *
	 * @var string
	 */
	const EXCEPTION = 'exception';

	/**
	 * Strict rules warning.
	 *
	 * @var string
	 */
	const STRICT = 'strict';

	/**
	 * Deprecated code usage warning.
	 *
	 * @var string
	 */
	const DEPRECATED = 'deprecated';

	/**
	 * Is debug enabled?
	 *
	 * @var boolean
	 */
	private static $debug = false;

	/**
	 * \Jyxo\ErrorMail instance for sending fatal error emails (used by the shutdown function and error handler).
	 *
	 * @var \Jyxo\ErrorMail
	 */
	private static $errorMail;

	/**
	 * Constructor preventing from creating class instances.
	 *
	 * @throws \LogicException When trying to create an instance
	 */
	public final function __construct()
	{
		throw new \LogicException(sprintf('It is forbidden to create instances of %s class.', get_class($this)));
	}

	/**
	 * Initializes error handling.
	 *
	 * @param boolean $debug Turn debugging on?
	 */
	public static function init($debug = false)
	{
		// Sets debugging
		self::$debug = (bool) $debug;

		// Registers handlers
		set_error_handler([__CLASS__, 'handleError']);
		set_exception_handler([__CLASS__, 'handleException']);
		register_shutdown_function([__CLASS__, 'handleFatalError']);
	}

	/**
	 * Sets \Jyxo\ErrorMail instance for sending fatal error emails (used by the shutdown function and error handler).
	 *
	 * @param \Jyxo\ErrorMail $errorMail
	 */
	public static function setErrorMail(\Jyxo\ErrorMail $errorMail)
	{
		self::$errorMail = $errorMail;
	}

	/**
	 * Handles errors and logs them.
	 *
	 * @param integer $type Error type
	 * @param string $message Error message
	 * @param string $file File where the error occurred
	 * @param integer $line Line on which the error occurred
	 * @param array $context Error context variables
	 * @return boolean Was the error processed?
	 */
	public static function handleError($type, $message, $file, $line, $context)
	{
		// 0 means the error was blocked by prepending "@" to the command or by error_reporting settings
		if (0 === ($type & error_reporting())) {
			return true;
		}

		static $types = [
			E_RECOVERABLE_ERROR => self::ERROR,
			E_USER_ERROR => self::ERROR,
			E_WARNING => self::WARNING,
			E_USER_WARNING => self::WARNING,
			E_NOTICE => self::NOTICE,
			E_USER_NOTICE => self::NOTICE,
			E_STRICT => self::STRICT,
			E_DEPRECATED => self::DEPRECATED,
			E_USER_DEPRECATED => self::DEPRECATED
		];

		// On false, the standard error handler will be used
		return self::log(
			[
				'type' => $types[$type],
				'text' => $message,
				'file' => $file,
				'line' => $line,
				'context' => $context,
				'trace' => array_slice(debug_backtrace(), 1) // Removes the error handler call from trace
			]
		);
	}

	/**
	 * Catches exceptions and logs them.
	 *
	 * @param \Exception $exception Uncaught exception
	 */
	public static function handleException(\Exception $exception)
	{
		self::exception($exception);
		if (self::$errorMail) {
			self::$errorMail->send($exception);
		}
	}

	/**
	 * Handles critical errors and logs them.
	 */
	public static function handleFatalError()
	{
		// List of critical errors
		static $fatalErrors = [
			E_ERROR => true,
			E_CORE_ERROR => true,
			E_COMPILE_ERROR => true,
			E_PARSE => true,
		];

		// If the last error was critical
		$error = error_get_last();
		if (isset($fatalErrors[$error['type']])) {
			self::log(
				[
					'type' => self::FATAL,
					'text' => $error['message'],
					'file' => $error['file'],
					'line' => $error['line']
				]
			);
			if (self::$errorMail) {
				$ex = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
				self::$errorMail->send($ex);
			}
		}
	}

	/**
	 * Adds a caught exception.
	 *
	 * @param \Exception $exception Caught exception
	 * @param boolean $fire Shall we use FirePHP?
	 */
	public static function exception(\Exception $exception, $fire = true)
	{
		self::log(
			[
				'type' => self::EXCEPTION,
				'text' => $exception->getMessage() . ' [' . $exception->getCode() . ']',
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTrace(),
				'previous' => self::getAllPreviousExceptions($exception)
			],
			$fire
		);
	}

	/**
	 * Logs a message.
	 *
	 * @param array $message Message definition
	 * @param boolean $fire Shall we use FirePHP?
	 * @return boolean Was logging successful?
	 */
	public static function log(array $message, $fire = true)
	{
		// Adds default values if missing
		if (!isset($message['file'])) {
			$message['file'] = null;
		}
		if (!isset($message['line'])) {
			$message['line'] = null;
		}
		if (!isset($message['trace'])) {
			$message['trace'] = [];
		}
		if (!isset($message['previous'])) {
			$message['previous'] = [];
		}

		// We don't want HTML tags and entities in the log
		if (ini_get('html_errors')) {
			$message['text'] = html_entity_decode(strip_tags($message['text']));
		}

		// Request type
		if (!empty($_SERVER['argv'])) {
			// CLI
			$request = implode(' ', $_SERVER['argv']);
		} else {
			// Apache
			$request = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
			$request .= $_SERVER['REQUEST_URI'];
		}

		// Base text
		$text = sprintf('%s [Request: %s]', $message['text'], $request);
		if (isset($message['file'], $message['line'])) {
			$text .= sprintf(' [%s: %s]', $message['file'], $message['line']);
		}
		$log = sprintf("%s: %s\n", strtoupper($message['type']), $text);

		// Trace
		$log .= self::getTraceLog($message['trace']);

		// Previous exceptions
		$previousTrace = $message['trace'];
		foreach ($message['previous'] as $previous) {
			// Throw away trace parts that have already been processed
			$trace = array_reverse(array_diff_key(array_reverse($previous->getTrace()), array_reverse($previousTrace)));
			$previousTrace = $previous->getTrace();

			$log .= sprintf("Previous: %s [%s] [%s: %s]\n", $previous->getMessage(), $previous->getCode(), $previous->getFile(), $previous->getLine());
			$log .= self::getTraceLog($trace);
		}

		// FirePHP log for debugging
		if (self::$debug && $fire) {
			self::firephp($message);
		}

		// Logging
		return error_log(trim($log));
	}

	/**
	 * Sends a message to FirePHP.
	 *
	 * @param array $message Message definition
	 * @return boolean Was sending successful?
	 */
	private static function firephp($message)
	{
		static $labels = [
			self::EXCEPTION => 'Exception',
			self::FATAL => 'Fatal Error',
			self::ERROR => 'Error',
			self::WARNING => 'Warning',
			self::NOTICE => 'Notice',
			self::STRICT => 'Strict',
			self::DEPRECATED => 'Deprecated'
		];

		// Adds to FirePHP
		return FirePhp::trace(
			sprintf('%s: %s', $labels[$message['type']], $message['text']),
			$message['file'],
			$message['line'],
			$message['trace']
		);
	}

	/**
	 * Returns trace log.
	 *
	 * @param array $trace Trace definition
	 * @return string
	 */
	private static function getTraceLog(array $trace)
	{
		$log = '';
		foreach ($trace as $levelNo => $level) {
			if (!isset($level['file'])) {
				$level['file'] = 0;
			}
			if (!isset($level['line'])) {
				$level['line'] = 0;
			}
			if (!isset($level['class'])) {
				$level['class'] = '';
			}
			if (!isset($level['type'])) {
				$level['type'] = '';
			}
			if (!isset($level['function'])) {
				$level['function'] = '';
			}
			$log .= sprintf("\t%s\t%s\t%s\t%s\n", $levelNo, $level['file'], $level['line'], $level['class'] . $level['type'] . $level['function']);
		}
		return $log;
	}

	/**
	 * Returns all exception's previous exceptions.
	 *
	 * @param \Exception $exception Exception to process
	 * @return array
	 */
	private static function getAllPreviousExceptions(\Exception $exception)
	{
		$stack = [];
		$previous = $exception->getPrevious();
		while (null !== $previous) {
			$stack[] = $previous;
			$previous = $previous->getPrevious();
		}
		return $stack;
	}
}
