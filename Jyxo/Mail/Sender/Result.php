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

namespace Jyxo\Mail\Sender;

use Jyxo\Spl\BaseObject;
use Jyxo\Time\Time;

/**
 * Sending result.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Result extends BaseObject
{

	/**
	 * Email Id.
	 *
	 * @var string
	 */
	private $messageId = '';

	/**
	 * Email source.
	 *
	 * @var string
	 */
	private $source = '';

	/**
	 * Sending time.
	 *
	 * @var Time
	 */
	private $datetime = null;

	/**
	 * Returns email Id.
	 *
	 * @return string
	 */
	public function getMessageId(): string
	{
		return $this->messageId;
	}

	/**
	 * Sets email Id.
	 *
	 * @param string $messageId Email Id
	 * @return Result
	 */
	public function setMessageId(string $messageId): self
	{
		$this->messageId = $messageId;

		return $this;
	}

	/**
	 * Returns email source.
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		return $this->source;
	}

	/**
	 * Sets email source.
	 *
	 * @param string $source
	 * @return Result
	 */
	public function setSource(string $source): self
	{
		$this->source = $source;

		return $this;
	}

	/**
	 * Returns sending time.
	 *
	 * @return Time
	 */
	public function getDatetime(): Time
	{
		return $this->datetime;
	}

	/**
	 * Sets sending time.
	 *
	 * @param Time $datetime Sending time
	 * @return Result
	 */
	public function setDatetime(Time $datetime): self
	{
		$this->datetime = $datetime;

		return $this;
	}

}
