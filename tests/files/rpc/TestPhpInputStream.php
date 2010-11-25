<?php

/**
 * Třída zajišťující testování práce s PHP input streamem.
 *
 * @author Jaroslav Hanslík <libs@jyxo.com>
 */
class TestPhpInputStream
{
	/**
	 * Obsah.
	 *
	 * @var string
	 */
	private static $content = '';

	/**
	 * Kolik bylo přečteno.
	 *
	 * @var integer
	 */
	private $read = 0;

	/**
	 * Zaregistruje protokol.
	 *
	 * @return boolean
	 */
	public static function register()
	{
		stream_wrapper_unregister('php');
		return stream_wrapper_register('php', __CLASS__);
	}

	/**
	 * Odregistruje protokol.
	 *
	 * @return boolean
	 */
	public static function unregister()
	{
		return stream_wrapper_restore('php');
	}

	/**
	 * Nastaví obsah.
	 *
	 * @param string $content
	 */
	public static function setContent($content)
	{
		self::$content = (string) $content;
	}

	/**
	 * Otevře soubor.
	 *
	 * @param string $path
	 * @param string $mode
	 * @param integer $options
	 * @param string $openedPath
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
	 * Uzavře soubor.
	 *
	 * @return boolean
	 */
	public function stream_close()
	{
		return true;
	}

	/**
	 * Čte ze souboru.
	 *
	 * @param integer $length
	 * @return string
	 */
	public function stream_read($length)
	{
		$data = substr(self::$content, $this->read, $length);

		$this->read += $length;

		return $data;
	}

	/**
	 * Zjistí, zda nejsme na konci souboru.
	 *
	 * @return boolean
	 */
	public function stream_eof()
	{
		return $this->read >= strlen(self::$content);
	}

	/**
	 * Zjistí informace o souboru.
	 *
	 * @return array
	 */
	public function stream_stat()
	{
		return array();
	}
}
