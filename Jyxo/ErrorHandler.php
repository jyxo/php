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

use ErrorException;
use Exception;
use LogicException;
use Throwable;
use function array_diff_key;
use function array_reverse;
use function array_slice;
use function debug_backtrace;
use function error_get_last;
use function error_log;
use function error_reporting;
use function html_entity_decode;
use function implode;
use function ini_get;
use function register_shutdown_function;
use function set_error_handler;
use function set_exception_handler;
use function sprintf;
use function strip_tags;
use function strtoupper;
use function trim;
use const E_COMPILE_ERROR;
use const E_CORE_ERROR;
use const E_DEPRECATED;
use const E_ERROR;
use const E_NOTICE;
use const E_PARSE;
use const E_RECOVERABLE_ERROR;
use const E_STRICT;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;

/**
 * Error and exception handler.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class ErrorHandler
{

	/**
	 * Notice.
	 */
	public const NOTICE = 'notice';

	/**
	 * Warning.
	 */
	public const WARNING = 'warning';

	/**
	 * Error.
	 */
	public const ERROR = 'error';

	/**
	 * Fatal error.
	 */
	public const FATAL = 'fatal';

	/**
	 * Exception.
	 */
	public const EXCEPTION = 'exception';

	/**
	 * Strict rules warning.
	 */
	public const STRICT = 'strict';

	/**
	 * Deprecated code usage warning.
	 */
	public const DEPRECATED = 'deprecated';

	/**
	 * Is debug enabled?
	 *
	 * @var bool
	 */
	private static $debug = false;

	/**
	 * \Jyxo\ErrorMail instance for sending fatal error emails (used by the shutdown function and error handler).
	 *
	 * @var ErrorMail
	 */
	private static $errorMail;

	/**
	 * Constructor preventing from creating class instances.
	 */
	final public function __construct()
	{
		throw new LogicException(sprintf('It is forbidden to create instances of %s class.', static::class));
	}

	/**
	 * Initializes error handling.
	 *
	 * @param bool $debug Turn debugging on?
	 */
	public static function init(bool $debug = false): void
	{
		// Sets debugging
		self::$debug = $debug;

		// Registers handlers
		set_error_handler([self::class, 'handleError']);
		set_exception_handler([self::class, 'handleException']);
		register_shutdown_function([self::class, 'handleFatalError']);
	}

	/**
	 * Sets \Jyxo\ErrorMail instance for sending fatal error emails (used by the shutdown function and error handler).
	 *
	 * @param ErrorMail $errorMail
	 */
	public static function setErrorMail(ErrorMail $errorMail): void
	{
		self::$errorMail = $errorMail;
	}

	/**
	 * Handles errors and logs them.
	 *
	 * @param int $type Error type
	 * @param string $message Error message
	 * @param string $file File where the error occurred
	 * @param int $line Line on which the error occurred
	 * @param array $context Error context variables
	 * @return bool Was the error processed?
	 */
	public static function handleError(int $type, string $message, string $file, int $line, array $context): bool
	{
		// 0 means the error was blocked by prepending "@" to the command or by error_reporting settings
		if (($type & error_reporting()) === 0) {
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
			E_USER_DEPRECATED => self::DEPRECATED,
		];

		// On false, the standard error handler will be used
		return self::log(
			[
				'type' => $types[$type],
				'text' => $message,
				'file' => $file,
				'line' => $line,
				'context' => $context,
				// Removes the error handler call from trace
				'trace' => array_slice(debug_backtrace(), 1),
			]
		);
	}

	/**
	 * Catches exceptions and logs them.
	 *
	 * @param Exception $exception Uncaught exception
	 */
	public static function handleException(Throwable $exception): void
	{
		self::exception($exception);

		if (self::$errorMail) {
			self::$errorMail->send($exception);
		}
	}

	/**
	 * Handles critical errors and logs them.
	 */
	public static function handleFatalError(): void
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

		if (!isset($fatalErrors[$error['type']])) {
			return;
		}

		self::log(
			[
				'type' => self::FATAL,
				'text' => $error['message'],
				'file' => $error['file'],
				'line' => $error['line'],
			]
		);

		if (!self::$errorMail) {
			return;
		}

		$ex = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
		self::$errorMail->send($ex);
	}

	/**
	 * Adds a caught exception.
	 *
	 * @param Exception $exception Caught exception
	 * @param bool $fire Shall we use FirePHP?
	 */
	public static function exception(Throwable $exception, bool $fire = true): bool
	{
		self::log(
			[
				'type' => self::EXCEPTION,
				'text' => $exception->getMessage() . ' [' . $exception->getCode() . ']',
				'file' => $exception->getFile(),
				'line' => $exception->getLine(),
				'trace' => $exception->getTrace(),
				'previous' => self::getAllPreviousExceptions($exception),
			],
			$fire
		);
	}

	/**
	 * Logs a message.
	 *
	 * @param array $message Message definition
	 * @param bool $fire Shall we use FirePHP?
	 * @return bool Was logging successful?
	 */
	public static function log(array $message, bool $fire = true): bool
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
			$request = $_SERVER['HTTP_HOST'] ?? '';
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

			$log .= sprintf(
				"Previous: %s [%s] [%s: %s]\n",
				$previous->getMessage(),
				$previous->getCode(),
				$previous->getFile(),
				$previous->getLine()
			);
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
	 * @return bool Was sending successful?
	 */
	private static function firephp(array $message): bool
	{
		static $labels = [
			self::EXCEPTION => 'Exception',
			self::FATAL => 'Fatal Error',
			self::ERROR => 'Error',
			self::WARNING => 'Warning',
			self::NOTICE => 'Notice',
			self::STRICT => 'Strict',
			self::DEPRECATED => 'Deprecated',
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
	private static function getTraceLog(array $trace): string
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

			$log .= sprintf(
				"\t%s\t%s\t%s\t%s\n",
				$levelNo,
				$level['file'],
				$level['line'],
				$level['class'] . $level['type'] . $level['function']
			);
		}

		return $log;
	}

	/**
	 * Returns all exception's previous exceptions.
	 *
	 * @param Exception $exception Exception to process
	 * @return array
	 */
	private static function getAllPreviousExceptions(Throwable $exception): array
	{
		$stack = [];
		$previous = $exception->getPrevious();

		while ($previous !== null) {
			$stack[] = $previous;
			$previous = $previous->getPrevious();
		}

		return $stack;
	}

}
