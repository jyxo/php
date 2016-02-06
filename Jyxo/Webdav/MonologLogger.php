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

/**
 * Bridge to a Monolog logger.
 *
 * @category Jyxo
 * @package Jyxo\Webdav
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Ondřej Nešpor
 */
class MonologLogger implements LoggerInterface
{

	/**
	 * Monolog logger.
	 *
	 * @var \Monolog\Logger
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
	 * @param \Monolog\Logger $logger Monolog logger
	 * @param int $level Message level
	 */
	public function __construct(\Monolog\Logger $logger, int $level)
	{
		$this->logger = $logger;
		$this->level = $level;
	}

	/**
	 * Logs the given message.
	 *
	 * @param string $message Message to be logged
	 */
	public function log(string $message)
	{
		$this->logger->addRecord($this->level, $message);
	}

}
