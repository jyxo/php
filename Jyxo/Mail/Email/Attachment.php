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

namespace Jyxo\Mail\Email;

/**
 * Email attachment.
 *
 * @category Jyxo
 * @package Jyxo\Mail
 * @subpackage Email
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
abstract class Attachment extends \Jyxo\Spl\Object
{
	/**
	 * Ordinary attachment.
	 *
	 * @var string
	 */
	const DISPOSITION_ATTACHMENT = 'attachment';

	/**
	 * Inline attachment.
	 *
	 * @var string
	 */
	const DISPOSITION_INLINE = 'inline';

	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $disposition = '';

	/**
	 * Contents.
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Name.
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Id.
	 *
	 * @var string
	 */
	protected $cid = '';

	/**
	 * Content mime-type.
	 *
	 * @var string
	 */
	protected $mimeType = '';

	/**
	 * Content encoding.
	 *
	 * @var string
	 */
	protected $encoding = '';

	/**
	 * Returns type.
	 *
	 * @return string
	 */
	public function getDisposition(): string
	{
		return $this->disposition;
	}

	/**
	 * Returns contents.
	 *
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * Sets contents.
	 *
	 * @param string $content Contents
	 * @return \Jyxo\Mail\Email\Attachment
	 */
	public function setContent(string $content): self
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Returns name.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Sets name.
	 *
	 * @param string $name Name
	 * @return \Jyxo\Mail\Email\Attachment
	 */
	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Returns Id.
	 *
	 * @return string
	 */
	public function getCid(): string
	{
		return $this->cid;
	}

	/**
	 * Returns content mime-type.
	 *
	 * @return string
	 */
	public function getMimeType(): string
	{
		return $this->mimeType;
	}

	/**
	 * Sets content mime-type.
	 *
	 * @param string $mimeType Mime-type
	 * @return \Jyxo\Mail\Email\Attachment
	 */
	public function setMimeType(string $mimeType): self
	{
		$this->mimeType = $mimeType;

		return $this;
	}

	/**
	 * Returns contents encoding.
	 *
	 * @return string
	 */
	public function getEncoding(): string
	{
		return $this->encoding;
	}

	/**
	 * Returns if the attachment is inline.
	 *
	 * @return boolean
	 */
	public function isInline(): bool
	{
		return self::DISPOSITION_INLINE === $this->disposition;
	}
}
