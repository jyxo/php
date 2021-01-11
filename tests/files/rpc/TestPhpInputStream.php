<?php declare(strict_types = 1);

/**
 * Class providing tests of work with PHP input stream.
 *
 * @author Jaroslav Hanslík
 */
class TestPhpInputStream
{

	/**
	 * How much was already read.
	 *
	 * @var int
	 */
	private $read = 0;

	/**
	 * Content.
	 *
	 * @var string
	 */
	private static $content = '';

	/**
	 * Opens file.
	 *
	 * @param string $path File path
	 * @param string $mode File mode
	 * @param int $options Options
	 * @param string $openedPath Opened path
	 * @return bool
	 */
	public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
	{
		$mode = trim($mode, 'tb');

		switch ($mode) {
			case 'r':
			case 'r+':
				$this->read = 0;

				return true;

			default:
				return false;
		}
	}

	/**
	 * Closes file.
	 *
	 * @return bool
	 */
	public function stream_close(): bool
	{
		return true;
	}

	/**
	 * Reads from file.
	 *
	 * @param int $length Read length
	 * @return string
	 */
	public function stream_read(int $length): string
	{
		$data = substr(self::$content, $this->read, $length);

		$this->read += $length;

		return $data ?: '';
	}

	public function stream_write(string $data): int
	{
		return strlen($data);
	}

	/**
	 * Determines if we have reached the end of the file.
	 *
	 * @return bool
	 */
	public function stream_eof(): bool
	{
		return $this->read >= strlen(self::$content);
	}

	/**
	 * Returns information about the file.
	 *
	 * @return array
	 */
	public function stream_stat(): array
	{
		return [];
	}

	/**
	 * Registers protocol.
	 *
	 * @return bool
	 */
	public static function register(): bool
	{
		stream_wrapper_unregister('php');

		return stream_wrapper_register('php', self::class);
	}

	/**
	 * Unregisters protocol.
	 *
	 * @return bool
	 */
	public static function unregister(): bool
	{
		return stream_wrapper_restore('php');
	}

	/**
	 * Sets content.
	 *
	 * @param string $content Content
	 */
	public static function setContent(string $content): void
	{
		self::$content = (string) $content;
	}

}
