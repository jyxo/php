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

/**
 * Email contents container.
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @subpackage Email
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class Email extends \Jyxo\Spl\SplObject
{
	/**
	 * Highest priority.
	 *
	 * @var integer
	 */
	const PRIORITY_HIGHEST = 1;

	/**
	 * High priority.
	 *
	 * @var integer
	 */
	const PRIORITY_HIGH = 2;

	/**
	 * Normal priority.
	 *
	 * @var integer
	 */
	const PRIORITY_NORMAL = 3;

	/**
	 * Low priority.
	 *
	 * @var integer
	 */
	const PRIORITY_LOW = 4;

	/**
	 * Lowest priority.
	 *
	 * @var integer
	 */
	const PRIORITY_LOWEST = 5;

	/**
	 * Subject.
	 *
	 * @var string
	 */
	private $subject = '';

	/**
	 * Email sender.
	 *
	 * @var \Jyxo\Mail\Email\Address|null
	 */
	private $from = null;

	/**
	 * List of recipients.
	 *
	 * @var array
	 */
	private $to = [];

	/**
	 * List of carbon copy recipients.
	 *
	 * @var array
	 */
	private $cc = [];

	/**
	 * List of blind carbon copy recipients.
	 *
	 * @var array
	 */
	private $bcc = [];

	/**
	 * Response recipient address.
	 *
	 * @var array
	 */
	private $replyTo = [];

	/**
	 * Reading confirmation recipient.
	 *
	 * @var \Jyxo\Mail\Email\Address|null
	 */
	private $confirmReadingTo = null;

	/**
	 * Id of the message this is a response to.
	 *
	 * @var string
	 */
	private $inReplyTo = '';

	/**
	 * References to previous messages in the thread.
	 *
	 * @var array
	 */
	private $references = [];

	/**
	 * Message priority.
	 *
	 * @var integer
	 */
	private $priority = 0;

	/**
	 * List of custom headers.
	 *
	 * @var array
	 */
	private $headers = [];

	/**
	 * Message body.
	 *
	 * @var \Jyxo\Mail\Email\Body
	 */
	private $body = null;

	/**
	 * List of attachments.
	 *
	 * @var array
	 */
	private $attachments = [];

	/**
	 * Returns subject.
	 *
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * Sets subject.
	 *
	 * @param string $subject Subject
	 * @return \Jyxo\Mail\Email
	 */
	public function setSubject(string $subject): self
	{
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Returns sender address.
	 *
	 * @return \Jyxo\Mail\Email\Address|null
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * Sets sender address.
	 *
	 * @param \Jyxo\Mail\Email\Address $from Message sender
	 * @return \Jyxo\Mail\Email
	 */
	public function setFrom(\Jyxo\Mail\Email\Address $from): self
	{
		$this->from = $from;

		return $this;
	}

	/**
	 * Returns list of message recipients.
	 *
	 * @return array
	 */
	public function getTo(): array
	{
		return $this->to;
	}

	/**
	 * Adds a recipient.
	 *
	 * @param \Jyxo\Mail\Email\Address $to New recipient
	 * @return \Jyxo\Mail\Email
	 */
	public function addTo(\Jyxo\Mail\Email\Address $to): self
	{
		$this->to[] = $to;

		return $this;
	}

	/**
	 * Returns list of carbon copy recipients.
	 *
	 * @return array
	 */
	public function getCc(): array
	{
		return $this->cc;
	}

	/**
	 * Adds a carbon copy recipient.
	 *
	 * @param \Jyxo\Mail\Email\Address $cc New recipient
	 * @return \Jyxo\Mail\Email
	 */
	public function addCc(\Jyxo\Mail\Email\Address $cc): self
	{
		$this->cc[] = $cc;

		return $this;
	}

	/**
	 * Returns list of blind carbon copy recipients.
	 *
	 * @return array
	 */
	public function getBcc(): array
	{
		return $this->bcc;
	}

	/**
	 * Adds a blind carbon copy recipient.
	 *
	 * @param \Jyxo\Mail\Email\Address $bcc New recipient
	 * @return \Jyxo\Mail\Email
	 */
	public function addBcc(\Jyxo\Mail\Email\Address $bcc): self
	{
		$this->bcc[] = $bcc;

		return $this;
	}

	/**
	 * Returns the 'ReplyTo' address.
	 *
	 * @return array
	 */
	public function getReplyTo(): array
	{
		return $this->replyTo;
	}

	/**
	 * Adds a 'ReplyTo' address.
	 *
	 * @param \Jyxo\Mail\Email\Address $replyTo
	 * @return \Jyxo\Mail\Email
	 */
	public function addReplyTo(\Jyxo\Mail\Email\Address $replyTo): self
	{
		$this->replyTo[] = $replyTo;

		return $this;
	}

	/**
	 * Returns a reading confirmation address.
	 *
	 * @return \Jyxo\Mail\Email\Address|null
	 */
	public function getConfirmReadingTo()
	{
		return $this->confirmReadingTo;
	}

	/**
	 * Sets a reading confirmation address.
	 *
	 * @param \Jyxo\Mail\Email\Address $confirmReadingTo Confirmation recipient
	 * @return \Jyxo\Mail\Email
	 */
	public function setConfirmReadingTo(\Jyxo\Mail\Email\Address $confirmReadingTo): self
	{
		$this->confirmReadingTo = $confirmReadingTo;

		return $this;
	}

	/**
	 * Sets Id of the message this is a response to.
	 *
	 * @param string $inReplyTo Message Id
	 * @param array $references Previous mail references
	 * @return \Jyxo\Mail\Email
	 */
	public function setInReplyTo(string $inReplyTo, array $references = []): self
	{
		$this->inReplyTo = $inReplyTo;
		$this->references = $references;

		return $this;
	}

	/**
	 * Returns Id of the message this is a response to.
	 *
	 * @return string
	 */
	public function getInReplyTo(): string
	{
		return $this->inReplyTo;
	}

	/**
	 * Returns references to previous messages in the thread.
	 *
	 * @return array
	 */
	public function getReferences(): array
	{
		return $this->references;
	}

	/**
	 * Returns message priority.
	 *
	 * @return integer
	 */
	public function getPriority(): int
	{
		return $this->priority;
	}

	/**
	 * Sets message priority.
	 *
	 * @param integer $priority Priority
	 * @return \Jyxo\Mail\Email
	 * @throws \InvalidArgumentException If an unknown priority was provided
	 */
	public function setPriority(int $priority): self
	{
		static $priorities = [
			self::PRIORITY_HIGHEST => true,
			self::PRIORITY_HIGH => true,
			self::PRIORITY_NORMAL => true,
			self::PRIORITY_LOW => true,
			self::PRIORITY_LOWEST => true
		];
		if (!isset($priorities[$priority])) {
			throw new \InvalidArgumentException(sprintf('Unknown priority %s', $priority));
		}

		$this->priority = (int) $priority;

		return $this;
	}

	/**
	 * Returns custom headers.
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * Adds a custom header.
	 *
	 * @param \Jyxo\Mail\Email\Header $header Header
	 * @return \Jyxo\Mail\Email
	 */
	public function addHeader(\Jyxo\Mail\Email\Header $header): self
	{
		$this->headers[] = $header;

		return $this;
	}

	/**
	 * Returns message body.
	 *
	 * @return \Jyxo\Mail\Email\Body
	 */
	public function getBody(): \Jyxo\Mail\Email\Body
	{
		return $this->body;
	}

	/**
	 * Sets message body.
	 *
	 * @param \Jyxo\Mail\Email\Body $body Body
	 * @return \Jyxo\Mail\Email
	 */
	public function setBody(\Jyxo\Mail\Email\Body $body): self
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * Returns attachments.
	 *
	 * @return array
	 */
	public function getAttachments(): array
	{
		return $this->attachments;
	}

	/**
	 * Adds an attachment.
	 *
	 * @param \Jyxo\Mail\Email\Attachment $attachment Attachment
	 * @return \Jyxo\Mail\Email
	 */
	public function addAttachment(\Jyxo\Mail\Email\Attachment $attachment): self
	{
		$this->attachments[] = $attachment;

		return $this;
	}

	/**
	 * Checks if the message contains any attachments.
	 *
	 * @return boolean
	 */
	public function hasInlineAttachments(): bool
	{
		foreach ($this->attachments as $attachment) {
			if ($attachment->isInline()) {
				return true;
			}
		}

		return false;
	}
}
