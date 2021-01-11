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

namespace Jyxo\Mail;

use ArrayIterator;
use InvalidArgumentException;
use Jyxo\Mail\Email\Address;
use Jyxo\Mail\Sender\RecipientUnknownException;
use Jyxo\Mail\Sender\Result;
use Jyxo\Mail\Sender\Smtp;
use Jyxo\Mail\Sender\SmtpException;
use Jyxo\StringUtil;
use Jyxo\Time\Time;
use function array_merge;
use function class_exists;
use function count;
use function current;
use function iconv;
use function implode;
use function in_array;
use function mail;
use function md5;
use function next;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function strlen;
use function strtr;
use function time;
use function trim;
use function uniqid;

/**
 * Class for sending emails.
 * Based on PhpMailer class (C) Copyright 2001-2003 Brent R. Matzelle
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Sender
{

	/**
	 * Send using the internal mail() function.
	 */
	public const MODE_MAIL = 'mail';

	/**
	 * Send using a SMTP server.
	 */
	public const MODE_SMTP = 'smtp';

	/**
	 * No sending.
	 * Useful if we actually don't want to send the message but just generate it.
	 */
	public const MODE_NONE = 'none';

	/**
	 * Maximum line length.
	 */
	public const LINE_LENGTH = 74;

	/**
	 * Line ending.
	 */
	public const LINE_END = "\n";

	/**
	 * Simple mail type.
	 */
	public const TYPE_SIMPLE = 'simple';

	/**
	 * Email with a HTML and plaintext part.
	 */
	public const TYPE_ALTERNATIVE = 'alternative';

	/**
	 * Email with a HTML and plaintext part and attachments.
	 */
	public const TYPE_ALTERNATIVE_ATTACHMENTS = 'alternative_attachments';

	/**
	 * Email with attachments.
	 */
	public const TYPE_ATTACHMENTS = 'attachments';

	/**
	 * Charset.
	 *
	 * @var string
	 */
	private $charset = 'iso-8859-2';

	/**
	 * Hostname.
	 *
	 * @var string
	 */
	private $hostname = '';

	/**
	 * X-Mailer header value.
	 *
	 * @var string
	 */
	private $xmailer = '';

	/**
	 * Mail encoding (8bit, 7bit, binary, base64, quoted-printable).
	 *
	 * @var string
	 */
	private $encoding = Encoding::QUOTED_PRINTABLE;

	/**
	 * Email instance to be sent.
	 *
	 * @var Email
	 */
	private $email = null;

	/**
	 * SMTP server.
	 *
	 * @var string
	 */
	private $smtpHost = 'localhost';

	/**
	 * SMTP port.
	 *
	 * @var int
	 */
	private $smtpPort = 25;

	/**
	 * SMTP HELO value.
	 *
	 * @var string
	 */
	private $smtpHelo = '';

	/**
	 * SMTP username.
	 *
	 * @var string
	 */
	private $smtpUser = '';

	/**
	 * SMTP password.
	 *
	 * @var string
	 */
	private $smtpPsw = '';

	/**
	 * SMTP connection timeout.
	 *
	 * @var string
	 */
	private $smtpTimeout = 5;

	/**
	 * Sending result.
	 *
	 * @var Result
	 */
	private $result = null;

	/**
	 * Generated boundaries of mail parts.
	 *
	 * @var array
	 */
	private $boundary = [];

	/**
	 * Sending mode.
	 *
	 * @var int
	 */
	private $mode = self::MODE_MAIL;

	/**
	 * Email type.
	 *
	 * @var string
	 */
	private $type = self::TYPE_SIMPLE;

	/**
	 * Generated email headers.
	 *
	 * @var array
	 */
	private $createdHeader = [];

	/**
	 * Generated email body.
	 *
	 * @var string
	 */
	private $createdBody = '';

	/**
	 * Returns charset.
	 *
	 * @return string
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Sets charset.
	 *
	 * @param string $charset Final charset
	 * @return Sender
	 */
	public function setCharset(string $charset): self
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Returns hostname.
	 *
	 * @return string
	 */
	public function getHostname(): string
	{
		if (empty($this->hostname)) {
			$this->hostname = $_SERVER['HTTP_HOST'] ?? 'localhost';
		}

		return $this->hostname;
	}

	/**
	 * Sets hostname.
	 *
	 * @param string $hostname Hostname
	 * @return Sender
	 */
	public function setHostname(string $hostname): self
	{
		$this->hostname = $hostname;

		return $this;
	}

	/**
	 * Returns X-Mailer header value.
	 *
	 * @return string
	 */
	public function getXmailer(): string
	{
		return $this->xmailer;
	}

	/**
	 * Sets X-Mailer header value.
	 *
	 * @param string $xmailer X-Mailer header value
	 * @return Sender
	 */
	public function setXmailer(string $xmailer): self
	{
		$this->xmailer = $xmailer;

		return $this;
	}

	/**
	 * Returns encoding.
	 *
	 * @return string
	 */
	public function getEncoding(): string
	{
		return $this->encoding;
	}

	/**
	 * Sets encoding.
	 *
	 * @param string $encoding Encoding
	 * @return Sender
	 */
	public function setEncoding(string $encoding): self
	{
		if (!Encoding::isCompatible($encoding)) {
			throw new InvalidArgumentException(sprintf('Incompatible encoding %s.', $encoding));
		}

		$this->encoding = $encoding;

		return $this;
	}

	/**
	 * Returns the email to be sent.
	 *
	 * @return Email|null
	 */
	public function getEmail(): ?Email
	{
		return $this->email;
	}

	/**
	 * Sets the email to be sent.
	 *
	 * @param Email $email Email instance
	 * @return Sender
	 */
	public function setEmail(Email $email): self
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Sets SMTP parameters.
	 *
	 * @param string $host Hostname
	 * @param int $port Port
	 * @param string $helo HELO value
	 * @param string $user Username
	 * @param string $password Password
	 * @param int $timeout Connection timeout
	 * @return Sender
	 */
	public function setSmtp(
		string $host,
		int $port = 25,
		string $helo = '',
		string $user = '',
		string $password = '',
		int $timeout = 5
	): self
	{
		$this->smtpHost = $host;
		$this->smtpPort = $port;
		$this->smtpHelo = !empty($helo) ? $helo : $this->getHostname();
		$this->smtpUser = $user;
		$this->smtpPsw = $password;
		$this->smtpTimeout = $timeout;

		return $this;
	}

	/**
	 * Sends an email using the given mode.
	 *
	 * @param string $mode Sending mode
	 * @return Result
	 */
	public function send(string $mode): Result
	{
		// Sending modes
		static $modes = [
			self::MODE_SMTP => true,
			self::MODE_MAIL => true,
			self::MODE_NONE => true,
		];

		if (!isset($modes[$mode])) {
			throw new InvalidArgumentException(sprintf('Unknown sending mode %s.', $mode));
		}

		$this->mode = $mode;

		// Check of required parameters
		if ($this->email->from === null) {
			throw new Sender\CreateException('No sender was set.');
		}

		if ((count($this->email->to) + count($this->email->cc) + count($this->email->bcc)) < 1) {
			throw new Sender\CreateException('No recipient was set.');
		}

		// Creates a result
		$this->result = new Sender\Result();

		// Creates an email
		$this->create();
		$body = trim($this->createdBody);

		if (empty($body)) {
			throw new Sender\CreateException('No body was created.');
		}

		// Choose the appropriate sending method
		switch ($this->mode) {
			case self::MODE_SMTP:
				$this->sendBySmtp();

				break;
			case self::MODE_MAIL:
				$this->sendByMail();

				break;
			case self::MODE_NONE:
				// Break missing intentionally

			default:
				// No sending
				break;
		}

		// Save the generated source code to the result
		$this->result->source = $this->getHeader() . $this->createdBody;

		// Flush of created email
		$this->createdHeader = [];
		$this->createdBody = '';

		return $this->result;
	}

	/**
	 * Sends an email using the mail() function.
	 */
	private function sendByMail(): void
	{
		$recipients = '';
		$iterator = new ArrayIterator($this->email->to);

		while ($iterator->valid()) {
			$recipients .= $this->formatAddress($iterator->current());

			$iterator->next();

			if ($iterator->valid()) {
				$recipients .= ', ';
			}
		}

		$subject = $this->changeCharset($this->clearHeaderValue($this->email->subject));

		if (!mail($recipients, $this->encodeHeader($subject), $this->createdBody, $this->getHeader(['To', 'Subject']))) {
			throw new Sender\Exception('Could not send the message.');
		}
	}

	/**
	 * Sends an email using a SMTP server.
	 */
	private function sendBySmtp(): void
	{
		if (!class_exists(Smtp::class)) {
			throw new Sender\Exception(sprintf('Could not sent the message. Required class %s is missing.', Smtp::class));
		}

		try {
			$smtp = new Sender\Smtp($this->smtpHost, $this->smtpPort, $this->smtpHelo, $this->smtpTimeout);
			$smtp->connect();

			if (!empty($this->smtpUser)) {
				$smtp->auth($this->smtpUser, $this->smtpPsw);
			}

			// Sender
			$smtp->from($this->email->from->email);

			// Recipients
			$unknownRecipients = [];

			foreach (array_merge($this->email->to, $this->email->cc, $this->email->bcc) as $recipient) {
				try {
					$smtp->recipient($recipient->email);
				} catch (SmtpException $e) {
					$unknownRecipients[] = $recipient->email;
				}
			}

			if (!empty($unknownRecipients)) {
				throw new Sender\RecipientUnknownException('Unknown email recipients.', 0, $unknownRecipients);
			}

			// Data
			$smtp->data($this->getHeader(), $this->createdBody);
			$smtp->disconnect();
		} catch (RecipientUnknownException $e) {
			$smtp->disconnect();

			throw $e;
		} catch (SmtpException $e) {
			$smtp->disconnect();

			throw new Sender\Exception('Cannot send email: ' . $e->getMessage());
		}
	}

	/**
	 * Creates an email.
	 */
	private function create(): void
	{
		$uniqueId = md5(uniqid((string) time()));
		$hostname = $this->clearHeaderValue($this->getHostname());

		// Unique email Id
		$this->result->messageId = $uniqueId . '@' . $hostname;

		// Sending time
		$this->result->datetime = Time::now();

		// Parts boundaries
		$this->boundary = [
			1 => '====b1' . $uniqueId . '====' . $hostname . '====',
			2 => '====b2' . $uniqueId . '====' . $hostname . '====',
		];

		// Determine the message type
		if (!empty($this->email->attachments)) {
			// Are there any attachments?
			$this->type = !empty($this->email->body->alternative) ? self::TYPE_ALTERNATIVE_ATTACHMENTS : self::TYPE_ATTACHMENTS;
		} else {
			// No attachments
			$this->type = !empty($this->email->body->alternative) ? self::TYPE_ALTERNATIVE : self::TYPE_SIMPLE;
		}

		// Creates header and body
		$this->createHeader();
		$this->createBody();
	}

	/**
	 * Creates header.
	 */
	private function createHeader(): void
	{
		$this->addHeaderLine('Date', $this->result->datetime->email);
		$this->addHeaderLine('Return-Path', '<' . $this->clearHeaderValue($this->email->from->email) . '>');

		$this->addHeaderLine('From', $this->formatAddress($this->email->from));
		$this->addHeaderLine('Subject', $this->encodeHeader($this->changeCharset($this->clearHeaderValue($this->email->subject))));

		if (!empty($this->email->to)) {
			$this->addHeaderLine('To', $this->formatAddressList($this->email->to));
		} elseif (empty($this->email->cc)) {
			// Only blind carbon copy recipients
			$this->addHeaderLine('To', 'undisclosed-recipients:;');
		}

		if (!empty($this->email->cc)) {
			$this->addHeaderLine('Cc', $this->formatAddressList($this->email->cc));
		}

		if (!empty($this->email->bcc)) {
			$this->addHeaderLine('Bcc', $this->formatAddressList($this->email->bcc));
		}

		if (!empty($this->email->replyTo)) {
			$this->addHeaderLine('Reply-To', $this->formatAddressList($this->email->replyTo));
		}

		if (!empty($this->email->confirmReadingTo)) {
			$this->addHeaderLine('Disposition-Notification-To', $this->formatAddress($this->email->confirmReadingTo));
		}

		if (!empty($this->email->priority)) {
			$this->addHeaderLine('X-Priority', (string) $this->email->priority);
		}

		$this->addHeaderLine('Message-ID', '<' . $this->result->messageId . '>');

		if (!empty($this->email->inReplyTo)) {
			$this->addHeaderLine('In-Reply-To', '<' . $this->clearHeaderValue($this->email->inReplyTo) . '>');
		}

		if (!empty($this->email->references)) {
			$references = $this->email->references;

			foreach ($references as $key => $reference) {
				$references[$key] = $this->clearHeaderValue($reference);
			}

			$this->addHeaderLine('References', '<' . implode('> <', $references) . '>');
		}

		if (!empty($this->xmailer)) {
			$this->addHeaderLine('X-Mailer', $this->changeCharset($this->clearHeaderValue($this->xmailer)));
		}

		$this->addHeaderLine('MIME-Version', '1.0');

		// Custom headers
		foreach ($this->email->headers as $header) {
			$this->addHeaderLine(
				$this->changeCharset($this->clearHeaderValue($header->name)),
				$this->encodeHeader($this->clearHeaderValue($header->value))
			);
		}

		switch ($this->type) {
			case self::TYPE_ATTACHMENTS:
				// Break missing intentionally

			case self::TYPE_ALTERNATIVE_ATTACHMENTS:
				$subtype = $this->email->hasInlineAttachments() ? 'related' : 'mixed';
				$this->addHeaderLine(
					'Content-Type',
					'multipart/' . $subtype . ';' . self::LINE_END . ' boundary="' . $this->boundary[1] . '"'
				);

				break;
			case self::TYPE_ALTERNATIVE:
				$this->addHeaderLine('Content-Type', 'multipart/alternative;' . self::LINE_END . ' boundary="' . $this->boundary[1] . '"');

				break;
			case self::TYPE_SIMPLE:
				// Break missing intentionally

			default:
				$contentType = $this->email->body->isHtml() ? 'text/html' : 'text/plain';

				$this->addHeaderLine('Content-Type', $contentType . '; charset="' . $this->clearHeaderValue($this->charset) . '"');
				$this->addHeaderLine('Content-Transfer-Encoding', $this->encoding);

				break;
		}
	}

	/**
	 * Creates body.
	 */
	private function createBody(): void
	{
		switch ($this->type) {
			case self::TYPE_ATTACHMENTS:
				$contentType = $this->email->body->isHtml() ? 'text/html' : 'text/plain';

				$this->createdBody .= $this->getBoundaryStart(
					$this->boundary[1],
					$contentType,
					$this->charset,
					$this->encoding
				) . self::LINE_END;
				$this->createdBody .= $this->encodeString($this->changeCharset($this->email->body->main), $this->encoding) . self::LINE_END;

				$this->createdBody .= $this->attachAll();

				break;
			case self::TYPE_ALTERNATIVE_ATTACHMENTS:
				$this->createdBody .= '--' . $this->boundary[1] . self::LINE_END;
				$this->createdBody .= 'Content-Type: multipart/alternative;' . self::LINE_END . ' boundary="' . $this->boundary[2] . '"' . self::LINE_END . self::LINE_END;
				$this->createdBody .= $this->getBoundaryStart(
					$this->boundary[2],
					'text/plain',
					$this->charset,
					$this->encoding
				) . self::LINE_END;
				$this->createdBody .= $this->encodeString(
					$this->changeCharset($this->email->body->alternative),
					$this->encoding
				) . self::LINE_END;
				$this->createdBody .= $this->getBoundaryStart(
					$this->boundary[2],
					'text/html',
					$this->charset,
					$this->encoding
				) . self::LINE_END;
				$this->createdBody .= $this->encodeString($this->changeCharset($this->email->body->main), $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->getBoundaryEnd($this->boundary[2]) . self::LINE_END;

				$this->createdBody .= $this->attachAll();

				break;
			case self::TYPE_ALTERNATIVE:
				$this->createdBody .= $this->getBoundaryStart(
					$this->boundary[1],
					'text/plain',
					$this->charset,
					$this->encoding
				) . self::LINE_END;
				$this->createdBody .= $this->encodeString(
					$this->changeCharset($this->email->body->alternative),
					$this->encoding
				) . self::LINE_END;
				$this->createdBody .= $this->getBoundaryStart(
					$this->boundary[1],
					'text/html',
					$this->charset,
					$this->encoding
				) . self::LINE_END;
				$this->createdBody .= $this->encodeString($this->changeCharset($this->email->body->main), $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->getBoundaryEnd($this->boundary[1]);

				break;
			case self::TYPE_SIMPLE:
				// Break missing intentionally

			default:
				$this->createdBody = $this->encodeString($this->changeCharset($this->email->body->main), $this->encoding);
				break;
		}
	}

	/**
	 * Adds all attachments to the email.
	 *
	 * @return string
	 */
	private function attachAll(): string
	{
		$mime = [];

		foreach ($this->email->attachments as $attachment) {
			$encoding = !empty($attachment->encoding) ? $attachment->encoding : Encoding::BASE64;
			$name = $this->changeCharset($this->clearHeaderValue($attachment->name));

			$mime[] = '--' . $this->boundary[1] . self::LINE_END;
			$mime[] = 'Content-Type: ' . $this->clearHeaderValue($attachment->mimeType) . ';' . self::LINE_END;
			$mime[] = ' name="' . $this->encodeHeader($name) . '"' . self::LINE_END;
			$mime[] = 'Content-Transfer-Encoding: ' . $encoding . self::LINE_END;

			if ($attachment->isInline()) {
				$mime[] = 'Content-ID: <' . $this->clearHeaderValue($attachment->cid) . '>' . self::LINE_END;
			}

			$mime[] = 'Content-Disposition: ' . $attachment->disposition . ';' . self::LINE_END;
			$mime[] = ' filename="' . $this->encodeHeader($name) . '"' . self::LINE_END . self::LINE_END;

			// Just fix line endings in case of encoded attachments, encode otherwise
			$mime[] = !empty($attachment->encoding)
				? StringUtil::fixLineEnding($attachment->content, self::LINE_END)
				: $this->encodeString($attachment->content, $encoding);
			$mime[] = self::LINE_END . self::LINE_END;
		}

		$mime[] = '--' . $this->boundary[1] . '--' . self::LINE_END;

		return implode('', $mime);
	}

	/**
	 * Returns headers except given lines.
	 * Various sending methods need some headers removed, because they add them on their own.
	 *
	 * @param array $except Headers to be removed
	 * @return string
	 */
	private function getHeader(array $except = []): string
	{
		$header = '';

		foreach ($this->createdHeader as $headerLine) {
			if (!in_array($headerLine['name'], $except, true)) {
				$header .= $headerLine['name'] . ': ' . $headerLine['value'] . self::LINE_END;
			}
		}

		return $header . self::LINE_END;
	}

	/**
	 * Formats an email address.
	 *
	 * @param Address $address Address
	 * @return string
	 */
	private function formatAddress(Address $address): string
	{
		$name = $this->changeCharset($this->clearHeaderValue($address->name));
		$email = $this->clearHeaderValue($address->email);

		// No name is set
		if (empty($name) || ($name === $email)) {
			return $email;
		}

		if (preg_match('~[\200-\377]~', $name)) {
			// High ascii
			$name = $this->encodeHeader($name);
		} elseif (preg_match('~[^\\w\\s!#\$%&\'*+/=?^_`{|}\~-]~', $name)) {
			// Dangerous characters
			$name = '"' . $name . '"';
		}

		return $name . ' <' . $email . '>';
	}

	/**
	 * Formats a list of addresses.
	 *
	 * @param array $addressList Array of addresses
	 * @return string
	 */
	private function formatAddressList(array $addressList): string
	{
		$formated = '';

		while ($address = current($addressList)) {
			$formated .= $this->formatAddress($address);

			if (next($addressList) !== false) {
				$formated .= ', ';
			}
		}

		return $formated;
	}

	/**
	 * Adds a header line.
	 *
	 * @param string $name Header name
	 * @param string $value Header value
	 */
	private function addHeaderLine(string $name, string $value): void
	{
		$this->createdHeader[] = [
			'name' => $name,
			'value' => trim($value),
		];
	}

	/**
	 * Encodes headers.
	 *
	 * @param string $string Header definition
	 * @return string
	 */
	private function encodeHeader(string $string): string
	{
		// There might be dangerous characters in the string
		$count = preg_match_all('~[^\040-\176]~', $string, $matches);

		if ($count === 0) {
			return $string;
		}

		// 7 is =? + ? + Q/B + ? + ?=
		$maxlen = 75 - 7 - strlen($this->charset);

		// We have to use base64 always, because Thunderbird has problems with quoted-printable
		$encoding = 'B';
		$maxlen -= $maxlen % 4;
		$encoded = $this->encodeString($string, Encoding::BASE64, $maxlen);

		// Splitting to multiple lines
		$encoded = trim(preg_replace('~^(.*)$~m', ' =?' . $this->clearHeaderValue($this->charset) . '?' . $encoding . '?\1?=', $encoded));

		return $encoded;
	}

	/**
	 * Encodes a string using the given encoding.
	 *
	 * @param string $string Input string
	 * @param string $encoding Encoding
	 * @param int $lineLength Line length
	 * @return string
	 */
	private function encodeString(string $string, string $encoding, int $lineLength = self::LINE_LENGTH): string
	{
		return Encoding::encode($string, $encoding, $lineLength, self::LINE_END);
	}

	/**
	 * Returns a beginning of an email part.
	 *
	 * @param string $boundary Boundary
	 * @param string $contentType Mime-type
	 * @param string $charset Charset
	 * @param string $encoding Encoding
	 * @return string
	 */
	private function getBoundaryStart(string $boundary, string $contentType, string $charset, string $encoding): string
	{
		$start = '--' . $boundary . self::LINE_END;
		$start .= 'Content-Type: ' . $contentType . '; charset="' . $this->clearHeaderValue($charset) . '"' . self::LINE_END;
		$start .= 'Content-Transfer-Encoding: ' . $encoding . self::LINE_END;

		return $start;
	}

	/**
	 * Returns an end of an email part.
	 *
	 * @param string $boundary Boundary
	 * @return string
	 */
	private function getBoundaryEnd(string $boundary): string
	{
		return self::LINE_END . '--' . $boundary . '--' . self::LINE_END;
	}

	/**
	 * Clears headers from line endings.
	 *
	 * @param string $string Headers definition
	 * @return string
	 */
	private function clearHeaderValue(string $string): string
	{
		return strtr(trim($string), "\r\n\t", '   ');
	}

	/**
	 * Converts a string from UTF-8 into the email encoding.
	 *
	 * @param string $string Input string
	 * @return string
	 */
	private function changeCharset(string $string): string
	{
		if ($this->charset !== 'utf-8') {
			// Triggers a notice on an invalid character
			$string = @iconv('utf-8', $this->charset . '//TRANSLIT', $string);

			if ($string === false) {
				$string = '';
			}
		}

		return $string;
	}

}
