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

namespace Jyxo\Beholder\TestCase;

use Jyxo\Beholder\Result;
use Jyxo\Beholder\TestCase;
use Throwable;
use function class_exists;
use function date;
use function sprintf;
use const DATE_RFC822;

/**
 * Tests SMTP server availability.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Smtp extends TestCase
{

	/**
	 * Hostname.
	 *
	 * @var string
	 */
	private $host;

	/**
	 * Recipient.
	 *
	 * @var string
	 */
	private $to;

	/**
	 * Sender.
	 *
	 * @var string
	 */
	private $from;

	/**
	 * Timeout.
	 *
	 * @var int
	 */
	private $timeout;

	/**
	 * Constructor.
	 *
	 * @param string $description Test description
	 * @param string $host Hostname
	 * @param string $to Recipient
	 * @param string $from Sender
	 * @param int $timeout Timeout
	 */
	public function __construct(string $description, string $host, string $to, string $from, int $timeout = 2)
	{
		parent::__construct($description);

		$this->host = $host;
		$this->to = $to;
		$this->from = $from;
		$this->timeout = $timeout;
	}

	/**
	 * Performs the test.
	 *
	 * @return Result
	 */
	public function run(): Result
	{
		// The \Jyxo\Mail\Sender\Smtp class is required
		if (!class_exists(\Jyxo\Mail\Sender\Smtp::class)) {
			return new Result(
				Result::NOT_APPLICABLE,
				sprintf('Class %s missing', \Jyxo\Mail\Sender\Smtp::class)
			);
		}

		try {
			$header = 'From: ' . $this->from . "\n";
			$header .= 'To: ' . $this->to . "\n";
			$header .= 'Subject: Beholder' . "\n";
			$header .= 'Date: ' . date(DATE_RFC822) . "\n";

			$smtp = new \Jyxo\Mail\Sender\Smtp($this->host, 25, $this->host, $this->timeout);
			$smtp->connect()
				->from($this->from)
				->recipient($this->to)
				->data($header, 'Beholder SMTP Test')
				->disconnect();
		} catch (Throwable $e) {
			$smtp->disconnect();

			return new Result(Result::FAILURE, sprintf('Send error %s', $this->host));
		}

		// OK
		return new Result(Result::SUCCESS, $this->host);
	}

}
