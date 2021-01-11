<?php declare(strict_types = 1);

/**
 * Class for filesystem access testing.
 *
 * @author Jaroslav Hanslík
 */
class TestFileSystemStream
{

	/**
	 * No error.
	 */
	const ERROR_NONE = 0;

	/**
	 * Write error.
	 */
	const ERROR_WRITE = 1;

	/**
	 * Read error.
	 */
	const ERROR_READ = 2;

	/**
	 * Delete error.
	 */
	const ERROR_DELETE = 3;

	/**
	 * Path to the current file.
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * How much was already read.
	 *
	 * @var int
	 */
	private $read = 0;

	/**
	 * Expected error.
	 *
	 * @var int
	 */
	private static $error = self::ERROR_NONE;

	/**
	 * Tested directory.
	 *
	 * @var string
	 */
	private static $dir = [];

	/**
	 * Opens a file.
	 *
	 * @param string $path File path
	 * @param string $mode Open mode
	 * @param int $options Additional options
	 * @param string|null $openedPath Opened file path
	 * @return bool
	 */
	public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
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
	 * @return bool
	 */
	public function stream_close(): bool
	{
		$this->path = '';

		return true;
	}

	/**
	 * Reads from a file.
	 *
	 * @param int $length Read length
	 * @return string
	 */
	public function stream_read(int $length): string
	{
		if (self::$error === self::ERROR_READ) {
			return '';
		}

		$data = substr(self::$dir[$this->path], $this->read, $length);

		$this->read += $length;

		return $data ?: '';
	}

	/**
	 * Writes into a file.
	 *
	 * @param string $data Data to be written
	 * @return int Number of bytes written
	 */
	public function stream_write(string $data): int
	{
		if (self::$error === self::ERROR_WRITE) {
			return 0;
		}

		self::$dir[$this->path] .= $data;

		return strlen($data);
	}

	/**
	 * Returns if the pointer is at the end of the file.
	 *
	 * @return bool
	 */
	public function stream_eof(): bool
	{
		return $this->read >= strlen(self::$dir[$this->path]);
	}

	/**
	 * Retrieves information about a file.
	 *
	 * @return array
	 */
	public function stream_stat(): array
	{
		return [];
	}

	/**
	 * Deletes a file.
	 *
	 * @param string $path File path
	 * @return bool
	 */
	public function unlink(string $path): bool
	{
		if (self::$error === self::ERROR_DELETE) {
			return false;
		}

		unset(self::$dir[$path]);

		return true;
	}

	/**
	 * Registers a protocol.
	 *
	 * @param string $protocol Protocol name
	 * @return bool
	 */
	public static function register(string $protocol): bool
	{
		return stream_wrapper_register((string) $protocol, self::class);
	}

	/**
	 * Unregisters a protocol.
	 *
	 * @param string $protocol Protocol name
	 * @return bool
	 */
	public static function unregister(string $protocol): bool
	{
		return stream_wrapper_unregister((string) $protocol);
	}

	/**
	 * Sets error type.
	 *
	 * @param int $error Error type
	 */
	public static function setError(int $error): void
	{
		self::$error = (int) $error;
	}

}
