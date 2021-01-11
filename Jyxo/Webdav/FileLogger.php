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

namespace Jyxo\Webdav;

use function date;
use function file_put_contents;
use function sprintf;
use const FILE_APPEND;

/**
 * File based WebDav logger.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Nešpor
 */
class FileLogger implements LoggerInterface
{

	/**
	 * Logging filename.
	 *
	 * @var string
	 */
	private $fileName;

	/**
	 * Creates the logger.
	 *
	 * @param string $fileName Logging filename
	 */
	public function __construct(string $fileName)
	{
		$this->fileName = $fileName;
	}

	/**
	 * Logs the given message.
	 *
	 * @param string $message Message to be logged
	 */
	public function log(string $message): void
	{
		file_put_contents($this->fileName, sprintf("[%s]: %s\n", date('Y-m-d H:i:s'), $message), FILE_APPEND);
	}

}
