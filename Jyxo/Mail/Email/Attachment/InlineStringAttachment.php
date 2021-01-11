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

namespace Jyxo\Mail\Email\Attachment;

use InvalidArgumentException;
use Jyxo\Mail\Email\Attachment;
use Jyxo\Mail\Encoding;
use function sprintf;

/**
 * Inline mail attachment created from a string.
 *
 * @copyright Copyright (c) 2005-2011 Jyxo, s.r.o.
 * @license https://github.com/jyxo/php/blob/master/license.txt
 * @author Jaroslav Hanslík
 */
class InlineStringAttachment extends Attachment
{

	/**
	 * Type.
	 *
	 * @var string
	 */
	protected $disposition = Attachment::DISPOSITION_INLINE;

	/**
	 * Creates an attachment.
	 *
	 * @param string $content FileAttachment contents
	 * @param string $name Attachment name
	 * @param string $cid Id
	 * @param string $mimeType Attachment mime-type
	 * @param string $encoding Source encoding
	 */
	public function __construct(
		string $content,
		string $name,
		string $cid,
		string $mimeType = 'application/octet-stream',
		string $encoding = ''
	)
	{
		$this->setContent($content);
		$this->setName($name);
		$this->setCid($cid);
		$this->setMimeType($mimeType);
		$this->setEncoding($encoding);
	}

	/**
	 * Sets Id.
	 *
	 * @param string $cid
	 * @return InlineStringAttachment
	 */
	public function setCid(string $cid): self
	{
		$this->cid = $cid;

		return $this;
	}

	/**
	 * Sets contents encoding.
	 * If none is set, assume no encoding is used.
	 *
	 * @param string $encoding Encoding name
	 * @return InlineStringAttachment
	 */
	public function setEncoding(string $encoding): self
	{
		if (!empty($encoding) && (!Encoding::isCompatible($encoding))) {
			throw new InvalidArgumentException(sprintf('Incompatible encoding %s', $encoding));
		}

		$this->encoding = $encoding;

		return $this;
	}

}
