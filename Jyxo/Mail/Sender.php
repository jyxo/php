<?php

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

/**
 * Class for sending emails.
 * Based on PhpMailer class (C) Copyright 2001-2003  Brent R. Matzelle
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @subpackage Sender
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Sender
{
	/**
	 * Send using the internal mail() function.
	 *
	 * @var string
	 */
	const MODE_MAIL = 'mail';

	/**
	 * Send using a SMTP server.
	 *
	 * @var string
	 */
	const MODE_SMTP = 'smtp';

	/**
	 * No sending.
	 * Useful if we actually don't want to send the message but just generate it.
	 *
	 * @var string
	 */
	const MODE_NONE = 'none';

	/**
	 * Maximum line length.
	 *
	 * @var integer
	 */
	const LINE_LENGTH = 74;

	/**
	 * Line ending.
	 *
	 * @var string
	 */
	const LINE_END = "\n";

	/**
	 * Simple mail type.
	 *
	 * @var string
	 */
	const TYPE_SIMPLE = 'simple';

	/**
	 * Email with a HTML and plaintext part.
	 *
	 * @var string
	 */
	const TYPE_ALTERNATIVE = 'alternative';

	/**
	 * Email with a HTML and plaintext part and attachments.
	 *
	 * @var string
	 */
	const TYPE_ALTERNATIVE_ATTACHMENTS = 'alternative_attachments';

	/**
	 * Email with attachments.
	 *
	 * @var string
	 */
	const TYPE_ATTACHMENTS = 'attachments';

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
	 * @var \Jyxo\Mail\Email
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
	 * @var integer
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
	 * @var \Jyxo\Mail\Sender\Result
	 */
	private $result = null;

	/**
	 * Generated boundaries of mail parts.
	 *
	 * @var array
	 */
	private $boundary = array();

	/**
	 * Sending mode.
	 *
	 * @var integer
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
	private $createdHeader = array();

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
	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * Sets charset.
	 *
	 * @param string $charset Final charset
	 * @return \Jyxo\Mail\Sender
	 */
	public function setCharset($charset)
	{
		$this->charset = (string) $charset;

		return $this;
	}

	/**
	 * Returns hostname.
	 *
	 * @return string
	 */
	public function getHostname()
	{
		if (empty($this->hostname)) {
			$this->hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
		}

		return $this->hostname;
	}

	/**
	 * Sets hostname.
	 *
	 * @param string $hostname Hostname
	 * @return \Jyxo\Mail\Sender
	 */
	public function setHostname($hostname)
	{
		$this->hostname = (string) $hostname;

		return $this;
	}

	/**
	 * Returns X-Mailer header value.
	 *
	 * @return string
	 */
	public function getXmailer()
	{
		return $this->xmailer;
	}

	/**
	 * Sets X-Mailer header value.
	 *
	 * @param string $xmailer X-Mailer header value
	 * @return \Jyxo\Mail\Sender
	 */
	public function setXmailer($xmailer)
	{
		$this->xmailer = (string) $xmailer;

		return $this;
	}

	/**
	 * Returns encoding.
	 *
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * Sets encoding.
	 *
	 * @param string $encoding Encoding
	 * @return \Jyxo\Mail\Sender
	 * @throws \InvalidArgumentException If an incompatible encoding was provided
	 */
	public function setEncoding($encoding)
	{
		if (!Encoding::isCompatible($encoding)) {
			throw new \InvalidArgumentException(sprintf('Incompatible encoding %s.', $encoding));
		}

		$this->encoding = (string) $encoding;

		return $this;
	}

	/**
	 * Returns the email to be sent.
	 *
	 * @return \Jyxo\Mail\Email
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Sets the email to be sent.
	 *
	 * @param \Jyxo\Mail\Email $email Email instance
	 * @return \Jyxo\Mail\Sender
	 */
	public function setEmail(\Jyxo\Mail\Email $email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Sets SMTP parameters.
	 *
	 * @param string $host Hostname
	 * @param integer $port Port
	 * @param string $helo HELO value
	 * @param string $user Username
	 * @param string $password Password
	 * @param integer $timeout Connection timeout
	 * @return \Jyxo\Mail\Sender
	 */
	public function setSmtp($host, $port = 25, $helo = '', $user = '', $password = '', $timeout = 5)
	{
		$this->smtpHost = (string) $host;
		$this->smtpPort = (int) $port;
		$this->smtpHelo = !empty($helo) ? (string) $helo : $this->getHostname();
		$this->smtpUser = (string) $user;
		$this->smtpPsw = (string) $password;
		$this->smtpTimeout = (int) $timeout;

		return $this;
	}

	/**
	 * Sends an email using the given mode.
	 *
	 * @param string $mode Sending mode
	 * @return \Jyxo\Mail\Sender\Result
	 * @throws \InvalidArgumentException If an unknown mode was requested
	 * @throws \Jyxo\Mail\Sender\Exception On error
	 * @throws \Jyxo\Mail\Sender\CreateException If a required setting is missing
	 */
	public function send($mode)
	{
		// Sending modes
		static $modes = array(
			self::MODE_SMTP => true,
			self::MODE_MAIL => true,
			self::MODE_NONE => true
		);
		if (!isset($modes[$mode])) {
			throw new \InvalidArgumentException(sprintf('Unknown sending mode %s.', $mode));
		}
		$this->mode = (string) $mode;

		// Check of required parameters
		if (null === $this->email->from) {
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
		$this->createdHeader = array();
		$this->createdBody = '';

		return $this->result;
	}

	/**
	 * Sends an email using the mail() function.
	 *
	 * @throws \Jyxo\Mail\Sender\Exception On error
	 */
	private function sendByMail()
	{
		$recipients = '';
		$iterator = new \ArrayIterator($this->email->to);
		while ($iterator->valid()) {
			$recipients .= $this->formatAddress($iterator->current());

			$iterator->next();
			if ($iterator->valid()) {
				$recipients .= ', ';
			}
		}

		$subject = $this->changeCharset($this->clearHeaderValue($this->email->subject));

		if (!mail($recipients, $this->encodeHeader($subject), $this->createdBody, $this->getHeader(array('To', 'Subject')))) {
			throw new Sender\Exception('Could not send the message.');
		}
	}

	/**
	 * Sends an email using a SMTP server.
	 *
	 * @throws \Jyxo\Mail\Sender\Exception On error
	 */
	private function sendBySmtp()
	{
		if (!class_exists('\Jyxo\Mail\Sender\Smtp')) {
			throw new Sender\Exception('Could not sent the message. Required class \Jyxo\Mail\Sender\Smtp is missing.');
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
			$unknownRecipients = array();
			foreach (array_merge($this->email->to, $this->email->cc, $this->email->bcc) as $recipient) {
				try {
					$smtp->recipient($recipient->email);
				} catch (\Jyxo\Mail\Sender\SmtpException $e) {
					$unknownRecipients[] = $recipient->email;
				}
			}
			if (!empty($unknownRecipients)) {
				throw new Sender\RecipientUnknownException('Unknown email recipients.', 0, $unknownRecipients);
			}

			// Data
			$smtp->data($this->getHeader(), $this->createdBody);
			$smtp->disconnect();
		} catch (\Jyxo\Mail\Sender\RecipientUnknownException $e) {
			$smtp->disconnect();
			throw $e;
		} catch (\Jyxo\Mail\Sender\SmtpException $e) {
			$smtp->disconnect();
			throw new Sender\Exception('Cannot send email: ' . $e->getMessage());
		}
	}

	/**
	 * Creates an email.
	 */
	private function create()
	{
		$uniqueId = md5(uniqid(time()));
		$hostname = $this->clearHeaderValue($this->getHostname());

		// Unique email Id
		$this->result->messageId = $uniqueId . '@' . $hostname;

		// Sending time
		$this->result->datetime = \Jyxo\Time\Time::now();

		// Parts boundaries
		$this->boundary = array(
			1 => '====b1' . $uniqueId . '====' . $hostname . '====',
			2 => '====b2' . $uniqueId . '====' . $hostname . '===='
		);

		// Determine the message type
		if (!empty($this->email->attachments)) {
			// Are there any attachments?
			if (!empty($this->email->body->alternative)) {
				// There is an alternative content
				$this->type = self::TYPE_ALTERNATIVE_ATTACHMENTS;
			} else {
				// No alternative content
				$this->type = self::TYPE_ATTACHMENTS;
			}
		} else {
			// No attachments
			if (!empty($this->email->body->alternative)) {
				// There is an alternative content
				$this->type = self::TYPE_ALTERNATIVE;
			} else {
				// No alternative content
				$this->type = self::TYPE_SIMPLE;
			}
		}

		// Creates header and body
		$this->createHeader();
		$this->createBody();
	}

	/**
	 * Creates header.
	 */
	private function createHeader()
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
			$this->addHeaderLine('X-Priority', $this->email->priority);
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
			$this->addHeaderLine($this->changeCharset($this->clearHeaderValue($header->name)), $this->encodeHeader($this->clearHeaderValue($header->value)));
		}

		switch ($this->type) {
			case self::TYPE_ATTACHMENTS:
				// Break missing intentionally
			case self::TYPE_ALTERNATIVE_ATTACHMENTS:
				$subtype = $this->email->hasInlineAttachments() ? 'related' : 'mixed';
				$this->addHeaderLine('Content-Type', 'multipart/' . $subtype . ';' . self::LINE_END . ' boundary="' . $this->boundary[1] . '"');
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
	private function createBody()
	{
		switch ($this->type) {
			case self::TYPE_ATTACHMENTS:
				$contentType = $this->email->body->isHtml() ? 'text/html' : 'text/plain';

				$this->createdBody .= $this->getBoundaryStart($this->boundary[1], $contentType, $this->charset, $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->encodeString($this->changeCharset($this->email->body->main), $this->encoding) . self::LINE_END;

				$this->createdBody .= $this->attachAll();
				break;
			case self::TYPE_ALTERNATIVE_ATTACHMENTS:
				$this->createdBody .= '--' . $this->boundary[1] . self::LINE_END;
				$this->createdBody .= 'Content-Type: multipart/alternative;' . self::LINE_END . ' boundary="' . $this->boundary[2] . '"' . self::LINE_END . self::LINE_END;
				$this->createdBody .= $this->getBoundaryStart($this->boundary[2], 'text/plain', $this->charset, $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->encodeString($this->changeCharset($this->email->body->alternative), $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->getBoundaryStart($this->boundary[2], 'text/html', $this->charset, $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->encodeString($this->changeCharset($this->email->body->main), $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->getBoundaryEnd($this->boundary[2]) . self::LINE_END;

				$this->createdBody .= $this->attachAll();
				break;
			case self::TYPE_ALTERNATIVE:
				$this->createdBody .= $this->getBoundaryStart($this->boundary[1], 'text/plain', $this->charset, $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->encodeString($this->changeCharset($this->email->body->alternative), $this->encoding) . self::LINE_END;
				$this->createdBody .= $this->getBoundaryStart($this->boundary[1], 'text/html', $this->charset, $this->encoding) . self::LINE_END;
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
	private function attachAll()
	{
		$mime = array();

		foreach ($this->email->attachments as $attachment) {
			$encoding = !empty($attachment->encoding) ? $attachment->encoding : Encoding::BASE64;
			$name = $this->changeCharset($this->clearHeaderValue($attachment->name));

			$mime[] = '--' . $this->boundary[1] . self::LINE_END;
			$mime[] = 'Content-Type: ' . $this->clearHeaderValue($attachment->mimeType) . ';' . self::LINE_END;
			$mime[] = ' name="' .  $this->encodeHeader($name) . '"' . self::LINE_END;
			$mime[] = 'Content-Transfer-Encoding: ' . $encoding . self::LINE_END;

			if ($attachment->isInline()) {
				$mime[] = 'Content-ID: <' . $this->clearHeaderValue($attachment->cid) . '>' . self::LINE_END;
			}

			$mime[] = 'Content-Disposition: ' . $attachment->disposition . ';' . self::LINE_END;
			$mime[] = ' filename="' . $this->encodeHeader($name) . '"' . self::LINE_END . self::LINE_END;

			// Just fix line endings in case of encoded attachments, encode otherwise
			$mime[] = !empty($attachment->encoding)
				? \Jyxo\String::fixLineEnding($attachment->content, self::LINE_END)
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
	private function getHeader(array $except = array())
	{
		$header = '';
		foreach ($this->createdHeader as $headerLine) {
			if (!in_array($headerLine['name'], $except)) {
				$header .= $headerLine['name'] . ': ' . $headerLine['value'] . self::LINE_END;
			}
		}

		return $header . self::LINE_END;
	}

	/**
	 * Formats an email address.
	 *
	 * @param \Jyxo\Mail\Email\Address $address Address
	 * @return string
	 */
	private function formatAddress(\Jyxo\Mail\Email\Address $address)
	{
		$name = $this->changeCharset($this->clearHeaderValue($address->name));
		$email = $this->clearHeaderValue($address->email);

		// No name is set
		if ((empty($name)) || ($name === $email)) {
			return $email;
		}

		if (preg_match('~[\200-\377]~', $name)) {
			// High ascii
			$name = $this->encodeHeader($name);
		} elseif (preg_match('~[^\w\s!#\$%&\'*+/=?^_`{|}\~-]~', $name)) {
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
	private function formatAddressList(array $addressList)
	{
		$formated = '';
		while ($address = current($addressList)) {
			$formated .= $this->formatAddress($address);

			if (false !== next($addressList)) {
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
	private function addHeaderLine($name, $value)
	{
		$this->createdHeader[] = array(
			'name' => $name,
			'value' => trim($value)
		);
	}

	/**
	 * Encodes headers.
	 *
	 * @param string $string Header definition
	 * @return string
	 */
	private function encodeHeader($string)
	{
		// There might be dangerous characters in the string
		$count = preg_match_all('~[^\040-\176]~', $string, $matches);
		if (0 === $count) {
			return $string;
		}

		// 7 is =? + ? + Q/B + ? + ?=
		$maxlen = 75 - 7 - strlen($this->charset);

		// Uses encoding with shorter output
		/*
		if (mb_strlen($string, $this->charset) / 3 < $count) {
			$encoding = 'B';
			$maxlen -= $maxlen % 4;
			$encoded = $this->encodeString($string, \Jyxo\Mail\Encoding::BASE64, $maxlen);
		} else {
			$encoding = 'Q';
			$encoded = $this->encodeString($string, \Jyxo\Mail\Encoding::QUOTED_PRINTABLE);
			$encoded = str_replace(array('?', ' '), array('=3F', '=20'), $encoded);
		}
		*/

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
	 * @param integer $lineLength Line length
	 * @return string
	 */
	private function encodeString($string, $encoding, $lineLength = self::LINE_LENGTH)
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
	private function getBoundaryStart($boundary, $contentType, $charset, $encoding)
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
	private function getBoundaryEnd($boundary)
	{
		return self::LINE_END . '--' . $boundary . '--' . self::LINE_END;
	}

	/**
	 * Clears headers from line endings.
	 *
	 * @param string $string Headers definition
	 * @return string
	 */
	private function clearHeaderValue($string)
	{
		return strtr(trim($string), "\r\n\t", '   ');
	}

	/**
	 * Converts a string from UTF-8 into the email encoding.
	 *
	 * @param string $string Input string
	 * @return string
	 */
	private function changeCharset($string)
	{
		if ('utf-8' !== $this->charset) {
			// Triggers a notice on an invalid character
			$string = @iconv('utf-8', $this->charset . '//TRANSLIT', $string);
		}

		return $string;
	}
}
