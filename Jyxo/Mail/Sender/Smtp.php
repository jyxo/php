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
 * Class for sending emails using a SMTP server.
 * Works in combination with \Jyxo\Mail\Sender.
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @subpackage Sender
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Smtp
{
	/**
	 * Line endings.
	 *
	 * @var string
	 */
	const LINE_END = "\r\n";

	/**
	 * Established connection.
	 *
	 * @var resource
	 */
	private $connection = null;

	/**
	 * SMTP server.
	 *
	 * @var string
	 */
	private $host = 'localhost';

	/**
	 * SMTP port.
	 *
	 * @var integer
	 */
	private $port = 25;

	/**
	 * SMTP HELO value.
	 *
	 * @var string
	 */
	private $helo = 'localhost';

	/**
	 * SMTP connection timeout.
	 *
	 * @var string
	 */
	private $timeout = 5;

	/**
	 * Creates an instance.
	 *
	 * @param string $host Server hostname
	 * @param integer $port Server port
	 * @param string $helo HELO value
	 * @param integer $timeout Connection timeout
	 */
	public function __construct(string $host = 'localhost', int $port = 25, string $helo = 'localhost', int $timeout = 5)
	{
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;
		$this->helo = $helo;
	}

	/**
	 * Destroys an instance and disconnects from the server.
	 */
	public function __destruct()
	{
		if (is_resource($this->connection)) {
			$this->disconnect();
		}
	}

	/**
	 * Connects to the SMTP server.
	 *
	 * @return \Jyxo\Mail\Sender\Smtp
	 * @throws \Jyxo\Mail\Sender\SmtpException If a connection error occurs
	 */
	public function connect(): self
	{
		$this->connection = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
		if (false === $this->connection) {
			throw new SmtpException('CONNECTION: ' . $errno . ' ' . $errstr);
		}

		// Reads the initial connection data
		$this->readData();

		// Sends EHLO/HELO
		$this->commandHelo();

		return $this;
	}

	/**
	 * Disconnects from server.
	 *
	 * @return \Jyxo\Mail\Sender\Smtp
	 */
	public function disconnect(): self
	{
		if (is_resource($this->connection)) {
			try {
				$this->reset();
				$this->writeData('QUIT');
				fclose($this->connection);
				$this->connection = null;
			} catch (\Exception $e) {
				// Disconnecting; ignore possible exceptions
			}
		}

		return $this;
	}

	/**
	 * Connects to the server using a username and password.
	 *
	 * @param string $user Username
	 * @param string $password Password
	 * @return \Jyxo\Mail\Sender\Smtp
	 * @throws \Jyxo\Mail\Sender\SmtpException On authentication error
	 */
	public function auth(string $user, string $password): self
	{
		$this->writeData('AUTH LOGIN');
		$response = $this->readData();
		if ('334' !== substr($response, 0, 3)) {
			throw new SmtpException('AUTH: ' . $response);
		}
		$this->writeData(base64_encode($user));
		$response = $this->readData();
		if ('334' !== substr($response, 0, 3)) {
			throw new SmtpException('AUTH: ' . $response);
		}
		$this->writeData(base64_encode($password));
		$response = $this->readData();
		if ('235' !== substr($response, 0, 3)) {
			throw new SmtpException('AUTH: ' . $response);
		}

		return $this;
	}

	/**
	 * Sets the sender.
	 *
	 * @param string $from Sender
	 * @return \Jyxo\Mail\Sender\Smtp
	 */
	public function from(string $from): self
	{
		$this->commandMailFrom($from);

		return $this;
	}

	/**
	 * Adds a recipient.
	 *
	 * @param string $recipient Recipient
	 * @return \Jyxo\Mail\Sender\Smtp
	 */
	public function recipient(string $recipient): self
	{
		$this->commandRcptTo($recipient);

		return $this;
	}

	/**
	 * Sends email headers and body.
	 *
	 * @param string $header Headers
	 * @param string $body Body
	 * @return \Jyxo\Mail\Sender\Smtp
	 * @throws \Jyxo\Mail\Sender\SmtpException On data sending error
	 */
	public function data(string $header, string $body): self
	{
		$lineEnds = [\Jyxo\Mail\Sender::LINE_END . '.' => self::LINE_END . '..', \Jyxo\Mail\Sender::LINE_END => self::LINE_END];
		$header = strtr($header, $lineEnds);
		$body = strtr($body, $lineEnds);
		if ('.' == $body[0]) {
			$body = '.' . $body;
		}

		$this->commandData();
		$this->writeData(trim($header));
		$this->writeData('');
		$this->writeData($body);
		$this->writeData('.');

		$response = $this->readData();
		if ('250' !== substr($response, 0, 3)) {
			throw new SmtpException('SEND: ' . $response);
		}

		return $this;
	}

	/**
	 * Resets previous commands.
	 *
	 * @return \Jyxo\Mail\Sender\Smtp
	 */
	public function reset(): self
	{
		$this->commandRset();

		return $this;
	}

	/**
	 * Sends the EHLO/HELO command.
	 *
	 * @throws \Jyxo\Mail\Sender\SmtpException On error
	 */
	private function commandHelo()
	{
		$this->writeData('EHLO ' . $this->helo);
		$response = $this->readData();
		if ('250' !== substr($response, 0, 3)) {
			$this->writeData('HELO ' . $this->helo);
			$response = $this->readData();
			if ('250' !== substr($response, 0, 3)) {
				throw new SmtpException('HELO: ' . $response);
			}
		}
	}

	/**
	 * Sends the MAIL FROM command.
	 *
	 * @param string $from
	 * @throws \Jyxo\Mail\Sender\SmtpException On error
	 */
	private function commandMailFrom(string $from)
	{
		$this->writeData('MAIL FROM: <' . $from . '>');
		$response = $this->readData();
		if ('250' !== substr($response, 0, 3)) {
			throw new SmtpException('MAIL FROM: ' . $response);
		}
	}

	/**
	 * Sends the RCPT TO command.
	 *
	 * @param string $recipient
	 * @throws \Jyxo\Mail\Sender\SmtpException On error
	 */
	private function commandRcptTo(string $recipient)
	{
		$this->writeData('RCPT TO: <' . $recipient . '>');
		$response = $this->readData();
		if ('250' !== substr($response, 0, 3)) {
			throw new SmtpException('RCPT TO: ' . $response);
		}
	}

	/**
	 * Sends the DATA command.
	 *
	 * @throws \Jyxo\Mail\Sender\SmtpException On error
	 */
	private function commandData()
	{
		$this->writeData('DATA');
		$response = $this->readData();
		if ('354' !== substr($response, 0, 3)) {
			throw new SmtpException('DATA: ' . $response);
		}
	}

	/**
	 * Sends the RSET command.
	 *
	 * @throws \Jyxo\Mail\Sender\SmtpException On error
	 */
	private function commandRset()
	{
		$this->writeData('RSET');
		$response = $this->readData();
		if ('250' !== substr($response, 0, 3)) {
			throw new SmtpException('RSET: ' . $response);
		}
	}

	/**
	 * Reads data from the server.
	 *
	 * @return string
	 */
	private function readData(): string
	{
		$data = '';
		while ($line = fgets($this->connection)) {
			$data .= $line;
			if (' ' == substr($line, 3, 1)) {
				break;
			}
		}
		return $data;
	}

	/**
	 * Sends data to the server.
	 *
	 * @param string $data Data
	 * @throws \Jyxo\Mail\Sender\SmtpException On error
	 */
	private function writeData(string $data)
	{
		if (!fwrite($this->connection, $data . self::LINE_END)) {
			throw new SmtpException('Error while writing data.');
		}
	}
}
