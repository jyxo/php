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

/**
 * Exception used when some recipients' addresses do not exist.
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @subpackage Sender
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class RecipientUnknownException extends \Jyxo\Mail\Sender\Exception
{
	/**
	 * List of non-existent addresses.
	 *
	 * @var array
	 */
	private $list = [];

	/**
	 * Creates an exception.
	 *
	 * @param string $message Exception message
	 * @param integer $code Exception code
	 * @param array $list List of non-existent addresses
	 */
	public function __construct(string $message = null, int $code = 0, array $list = [])
	{
		parent::__construct($message, $code);
		$this->list = $list;
	}

	/**
	 * Returns the list of non-existent addresses.
	 *
	 * @return array
	 */
	public function getList(): array
	{
		return $this->list;
	}
}
