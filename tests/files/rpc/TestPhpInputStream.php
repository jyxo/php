<?php

/**
 * Class providing tests of work with PHP input stream.
 *
 * @author Jaroslav Hanslík
 */
class TestPhpInputStream
{
	/**
	 * Content.
	 *
	 * @var string
	 */
	private static $content = '';

	/**
	 * How much was already read.
	 *
	 * @var integer
	 */
	private $read = 0;

	/**
	 * Registers protocol.
	 *
	 * @return boolean
	 */
	public static function register()
	{
		stream_wrapper_unregister('php');
		return stream_wrapper_register('php', __CLASS__);
	}

	/**
	 * Unregisters protocol.
	 *
	 * @return boolean
	 */
	public static function unregister()
	{
		return stream_wrapper_restore('php');
	}

	/**
	 * Sets content.
	 *
	 * @param string $content Content
	 */
	public static function setContent($content)
	{
		self::$content = (string) $content;
	}

	/**
	 * Opens file.
	 *
	 * @param string $path File path
	 * @param string $mode File mode
	 * @param integer $options Options
	 * @param string $openedPath Opened path
	 * @return boolean
	 */
	public function stream_open($path, $mode, $options, &$openedPath)
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
	 * @return boolean
	 */
	public function stream_close()
	{
		return true;
	}

	/**
	 * Reads from file.
	 *
	 * @param integer $length Read length
	 * @return string
	 */
	public function stream_read($length)
	{
		$data = substr(self::$content, $this->read, $length);

		$this->read += $length;

		return $data;
	}

	/**
	 * Determines if we have reached the end of the file.
	 *
	 * @return boolean
	 */
	public function stream_eof()
	{
		return $this->read >= strlen(self::$content);
	}

	/**
	 * Returns information about the file.
	 *
	 * @return array
	 */
	public function stream_stat()
	{
		return array();
	}
}
