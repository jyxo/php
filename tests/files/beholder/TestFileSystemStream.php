<?php

/**
 * Class for filesystem access testing.
 *
 * @author Jaroslav Hanslík
 */
class TestFileSystemStream
{
	/**
	 * No error.
	 *
	 * @var integer
	 */
	const ERROR_NONE = 0;

	/**
	 * Write error.
	 *
	 * @var integer
	 */
	const ERROR_WRITE = 1;

	/**
	 * Read error.
	 *
	 * @var integer
	 */
	const ERROR_READ = 2;

	/**
	 * Delete error.
	 *
	 * @var integer
	 */
	const ERROR_DELETE = 3;

	/**
	 * Expected error.
	 *
	 * @var integer
	 */
	private static $error = self::ERROR_NONE;

	/**
	 * Tested directory.
	 *
	 * @var string
	 */
	private static $dir = array();

	/**
	 * Path to the current file.
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * How much was already read.
	 *
	 * @var integer
	 */
	private $read = 0;

	/**
	 * Registers a protocol.
	 *
	 * @param string $protocol Protocol name
	 * @return boolean
	 */
	public static function register($protocol)
	{
		return stream_wrapper_register((string) $protocol, __CLASS__);
	}

	/**
	 * Unregisters a protocol.
	 *
	 * @param string $protocol Protocol name
	 * @return boolean
	 */
	public static function unregister($protocol)
	{
		return stream_wrapper_unregister((string) $protocol);
	}

	/**
	 * Sets error type.
	 *
	 * @param integer $error Error type
	 */
	public static function setError($error)
	{
		self::$error = (int) $error;
	}

	/**
	 * Opens a file.
	 *
	 * @param string $path File path
	 * @param string $mode Open mode
	 * @param integer $options Additional options
	 * @param string $openedPath Opened file path
	 * @return boolean
	 */
	public function stream_open($path, $mode, $options, &$openedPath)
	{
		$mode = trim($mode, 'tb');
		switch ($mode) {
			case 'r':
			case 'r+':
			case 'w':
			case 'w+':
				$this->path = $path;
				if (!isset(self::$dir[$this->path])) {
					self::$dir[$this->path] = '';
				}
				$this->read = 0;
				return true;
			default:
				return false;
		}
	}

	/**
	 * Closes a file.
	 *
	 * @return boolean
	 */
	public function stream_close()
	{
		$this->path = '';

		return true;
	}

	/**
	 * Reads from a file.
	 *
	 * @param integer $length Read length
	 * @return string
	 */
	public function stream_read($length)
	{
		if (self::ERROR_READ === self::$error) {
			return '';
		}

		$data = substr(self::$dir[$this->path], $this->read, $length);

		$this->read += $length;

		return $data;
	}

	/**
	 * Writes into a file.
	 *
	 * @param string $data Data to be written
	 * @return integer Number of bytes written
	 */
	public function stream_write($data)
	{
		if (self::ERROR_WRITE === self::$error) {
			return 0;
		}

		self::$dir[$this->path] .= $data;

		return strlen($data);
	}

	/**
	 * Returns if the pointer is at the end of the file.
	 *
	 * @return boolean
	 */
	public function stream_eof()
	{
		return $this->read >= strlen(self::$dir[$this->path]);
	}

	/**
	 * Retrieves information about a file.
	 *
	 * @return array
	 */
	public function stream_stat()
	{
		return array();
	}

	/**
	 * Deletes a file.
	 *
	 * @param string $path File path
	 * @return boolean
	 */
	public function unlink($path)
	{
		if (self::ERROR_DELETE === self::$error) {
			return false;
		}

		unset(self::$dir[$path]);

		return true;
	}
}
