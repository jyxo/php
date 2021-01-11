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

namespace Jyxo;

use Exception;
use Throwable;
use function array_filter;
use function count;
use function file_get_contents;
use function file_put_contents;
use function get_class;
use function implode;
use function is_file;
use function mail;
use function print_r;
use function str_repeat;
use function strlen;
use function time;

/**
 * Class for sending error emails (can be used in register_shutdown_function, etc.)
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jakub Tománek
 */
class ErrorMail
{

	/**
	 * Minimal interval between sending two mails; to prevent from mailserver flooding.
	 */
	public const SEND_INTERVAL = 600;

	/**
	 * Path to the file with last sending timestamp.
	 *
	 * @var string
	 */
	private $timerFile;

	/**
	 * Additional headers.
	 *
	 * @var array
	 */
	private $headers = [];

	/**
	 * Mail recipients.
	 *
	 * @var array
	 */
	private $email = [];

	/**
	 * Constructor.
	 *
	 * @param string $timerFile Path to the file with last sending timestamp
	 * @param string|array $recipients Recipient(s)
	 * @param string $sender Mail sender
	 */
	public function __construct(string $timerFile, $recipients, string $sender)
	{
		$this->timerFile = $timerFile;
		$this->email = (array) $recipients;
		$this->headers[] = 'From: ' . $sender;
	}

	/**
	 * Sends the error email.
	 *
	 * @param Exception $e Caught exception
	 * @param bool $forceTimer Ignore timer (Always send)
	 */
	public function send(Throwable $e, bool $forceTimer = false): void
	{
		if ($forceTimer || $this->timerOutdated()) {
			$this->mail($this->createMail($e));
			file_put_contents($this->timerFile, time());
		}
	}

	/**
	 * Checks if we can send another email right now.
	 *
	 * @return bool
	 */
	private function timerOutdated(): bool
	{
		$send = true;

		if (is_file($this->timerFile)) {
			$contents = file_get_contents($this->timerFile);
			$next = $contents + self::SEND_INTERVAL;

			if ($next > time()) {
				// Next timestamp is in the future
				$send = false;
			}
		}

		return $send;
	}

	/**
	 * Creates an error email from an exception.
	 *
	 * @param Exception $e Caught exception
	 * @return array
	 */
	private function createMail(Throwable $e): array
	{
		$subject = get_class($e);

		if (!empty($_SERVER['SERVER_NAME'])) {
			$subject .= ': ' . $_SERVER['SERVER_NAME'];
		}

		$data = [
			'Exception' => '[' . $e->getCode() . '] ' . $e->getMessage(),
			'File' => $e->getFile() . '@' . $e->getLine(),
			'Trace' => $e->getTraceAsString(),
			'GET' => count($_GET) ? print_r($_GET, true) : null,
			'POST' => count($_POST) ? print_r($_POST, true) : null,
			'SERVER' => print_r($_SERVER, true),
		];
		// Remove empty GET and POST definitions
		$data = array_filter($data);

		$message = '';

		foreach ($data as $key => $val) {
			$message .= $key . "\n" . str_repeat('-', strlen($key)) . "\n";
			$message .= $val . "\n\n";
		}

		return [$subject, $message];
	}

	/**
	 * Actually sends an email.
	 *
	 * @param array $data Array(subject, body)
	 */
	private function mail(array $data): void
	{
		[$subject, $message] = $data;
		@mail(implode(', ', $this->email), $subject, $message, implode("\r\n", $this->headers));
	}

}
