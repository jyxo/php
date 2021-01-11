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

namespace Jyxo\Rpc;

use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use function call_user_func;
use function call_user_func_array;
use function class_exists;
use function date;
use function explode;
use function file_put_contents;
use function function_exists;
use function method_exists;
use function print_r;
use function sprintf;
use function strpos;
use function strtr;
use function trim;
use const FILE_APPEND;

/**
 * Class for creating a RPC server.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
abstract class Server
{

	/**
	 * Aliases of real functions.
	 *
	 * @var array
	 */
	private $aliases = [];

	/**
	 * Log file name.
	 *
	 * @var string
	 */
	private $logFile;

	/**
	 * Function that is called prior to saving a message into logfile.
	 * Can be used e. g. for wiping out private data (passwords) from log messages.
	 *
	 * @var callback
	 */
	private $logCallback;

	/**
	 * Processes a request and sends a RPC response.
	 */
	abstract public function process(): void;

	/**
	 * Actually registers a function to a server method.
	 *
	 * @param string $func Function name
	 */
	abstract protected function register(string $func): void;

	/**
	 * Creates a class instance.
	 */
	protected function __construct()
	{
		// Nothing
	}

	/**
	 * Returns class instance.
	 *
	 * @return Server
	 */
	public static function getInstance(): self
	{
		static $instance;

		if ($instance === null) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * Destroys a class instance.
	 */
	public function __destruct()
	{
		// Nothing
	}

	/**
	 * Turns on logging.
	 *
	 * @param string $filename Log file path.
	 * @param callback $callback Function to be called prior to logging a message.
	 * @return Server
	 */
	public function enableLogging(string $filename, ?callable $callback = null): self
	{
		$filename = trim($filename);

		// A log file has to be provided
		if (empty($filename)) {
			throw new InvalidArgumentException('No log file was provided.');
		}

		$this->logFile = $filename;
		$this->logCallback = $callback;

		return $this;
	}

	/**
	 * Registers class public methods.
	 *
	 * @param string $class Class name
	 * @param bool $useFullName Register with class name
	 * @return Server
	 */
	public function registerClass(string $class, bool $useFullName = true): self
	{
		if (!class_exists($class)) {
			throw new InvalidArgumentException(sprintf('Class %s does not exist.', $class));
		}

		$reflection = new ReflectionClass($class);

		foreach ($reflection->getMethods() as $method) {
			// Only public methods
			if ($method->isPublic()) {
				$func = $class . '::' . $method->getName();

				// Save short name as an alias
				if (!$useFullName) {
					$this->aliases[$method->getName()] = $func;
					$func = $method->getName();
				}

				$this->register($func);
			}
		}

		return $this;
	}

	/**
	 * Registers given method of given class.
	 * Method does not necessarily have to exist if __call or __callStatic method is defined.
	 *
	 * @param string $class Class name
	 * @param string $method Function name
	 * @param bool $useFullName Register with class name
	 * @return Server
	 */
	public function registerMethod(string $class, string $method, bool $useFullName = true): self
	{
		if (!class_exists($class)) {
			throw new InvalidArgumentException(sprintf('Třída %s neexistuje.', $class));
		}

		// If magic methods exist, always register
		if ((!method_exists($class, '__call')) && (!method_exists($class, '__callStatic'))) {
			try {
				$reflection = new ReflectionMethod($class, $method);
			} catch (ReflectionException $e) {
				throw new InvalidArgumentException(sprintf('Method %s::%s does not exist.', $class, $method));
			}

			// Only public methods
			if (!$reflection->isPublic()) {
				throw new InvalidArgumentException(sprintf('Method %s::%s is not public.', $class, $method));
			}
		}

		$func = $class . '::' . $method;

		// Save short name as an alias
		if (!$useFullName) {
			$this->aliases[$method] = $func;
			$func = $method;
		}

		$this->register($func);

		return $this;
	}

	/**
	 * Registers given function.
	 *
	 * @param string $func Function name
	 * @return Server
	 */
	public function registerFunc(string $func): self
	{
		if (!function_exists($func)) {
			throw new InvalidArgumentException(sprintf('Function %s does not exist.', $func));
		}

		$this->register($func);

		return $this;
	}

	/**
	 * Calls a server method with given parameters.
	 *
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @return mixed
	 */
	protected function call(string $method, array $params)
	{
		$func = $method;

		// If an alias was given, use the actual method
		if (isset($this->aliases[$method])) {
			$func = $this->aliases[$method];
		}

		// Class method
		if (strpos($func, '::') !== false) {
			[$className, $methodName] = explode('::', $func);

			try {
				// Method exists
				$reflection = new ReflectionMethod($className, $methodName);

				$callback = $reflection->isStatic() ? [$className, $methodName] : [new $className(), $methodName];
			} catch (ReflectionException $e) {
				// Method does not exist
				$callback = method_exists($className, '__call') ? [new $className(), $methodName] : [$className, $methodName];
			}
		} else {
			// Simple function
			$callback = $func;
		}

		$result = call_user_func_array($callback, $params);

		// Logging
		$this->log($method, $params, $result);

		return $result;
	}

	/**
	 * Logs a request.
	 *
	 * @param string $method Method name
	 * @param array $params Method parameters
	 * @param mixed $result Function result
	 */
	private function log(string $method, array $params, $result): void
	{
		// Log only if a filename is set
		if (!empty($this->logFile)) {
			// If a callback function is defined, call it
			if (!empty($this->logCallback)) {
				[$method, $params, $result] = call_user_func($this->logCallback, $method, $params, $result);
			}

			// Method
			$text = sprintf("Method: %s\n", $method);

			// Parameters
			foreach ($params as $paramName => $param) {
				$text .= sprintf("Param %s: %s\n", $paramName, trim(print_r($param, true)));
			}

			// Result
			$text .= sprintf("Result: %s\n", trim(print_r($result, true)));

			// Indent following lines
			$text = strtr(trim($text), ["\n" => "\n\t"]);

			// Time, ip address, hostname, uri
			$text = sprintf(
				"[%s] %s %s %s\n\t%s\n",
				date('Y-m-d H:i:s'),
				$_SERVER['REMOTE_ADDR'],
				$_SERVER['HTTP_HOST'],
				$_SERVER['REQUEST_URI'],
				$text
			);

			// Save into logfile
			file_put_contents($this->logFile, $text, FILE_APPEND);
		}
	}

	/**
	 * Prevents from singleton cloning.
	 */
	final public function __clone()
	{
		throw new LogicException(sprintf('Class %s can have only one instance.', static::class));
	}

}
