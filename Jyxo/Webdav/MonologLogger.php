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

use Monolog\Logger;

/**
 * Bridge to a Monolog logger.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Nešpor
 */
class MonologLogger implements LoggerInterface
{

	/**
	 * Monolog logger.
	 *
	 * @var Logger
	 */
	private $logger;

	/**
	 * Message level
	 *
	 * @var int
	 */
	private $level;

	/**
	 * Creates the logger.
	 *
	 * @param Logger $logger Monolog logger
	 * @param int $level Message level
	 */
	public function __construct(Logger $logger, int $level)
	{
		$this->logger = $logger;
		$this->level = $level;
	}

	/**
	 * Logs the given message.
	 *
	 * @param string $message Message to be logged
	 */
	public function log(string $message): void
	{
		$this->logger->addRecord($this->level, $message);
	}

}
